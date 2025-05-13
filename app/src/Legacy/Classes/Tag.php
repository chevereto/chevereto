<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use InvalidArgumentException;
use LogicException;
use PDO;
use function Chevereto\Legacy\assertNotStopWords;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Vars\env;

/**
 * Tags on the database are "as is" without any encoding
 * Tags on URL must be urlencoded
 * Tags printed to HTML must be under urldecode and htmlspecialchars
 */
final class Tag
{
    public const MAX_LENGTH = 32;

    public const FORBIDDEN_TAGS = [
        '/',
        '\\',
        ',',
        '.',
        '?',
        '&',
        '=',
        '#',
        '%',
        '+',
        '@',
        '!',
        '*',
        ';',
        ':',
        ' ',
    ];

    public const FORBIDDEN_CHARS = [
        ',',
        '/',
        '#',
    ];

    public static function assert(string $name, int $minLength = 1): void
    {
        $minLength = max(1, $minLength);
        if (mb_strlen($name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('String is too long');
        }
        if (mb_strlen($name) < $minLength) {
            throw new InvalidArgumentException('String is too short');
        }
        foreach (static::FORBIDDEN_CHARS as $forbidden) {
            if (str_contains($name, $forbidden)) {
                throw new InvalidArgumentException('Forbidden characters');
            }
        }
        foreach (static::FORBIDDEN_TAGS as $forbidden) {
            if (str_contains($name, $forbidden)
                && count(array_count_values(mb_str_split($name))) == 1
            ) {
                throw new InvalidArgumentException('Forbidden key');
            }
        }
        assertNotStopWords($name);
    }

    /**
     * @return array<int, string> tag names
     */
    public static function parse(string $tags): array
    {
        $tags = preg_replace('/\s+/', ' ', $tags);
        $array = explode(',', $tags);
        $array = array_map(function (string $value) {
            return trim($value);
        }, $array);
        $array = array_unique($array);
        $array = array_filter($array);
        $return = [];
        $maxTagsPerFile = (int) (env()['CHEVERETO_MAX_TAGS_PER_FILE'] ?? 0);
        $tagCount = 0;
        foreach ($array as $tag) {
            try {
                self::assert($tag);
                $tagCount++;
                if ($maxTagsPerFile > 0
                    && $tagCount > $maxTagsPerFile
                ) {
                    break;
                }
                $return[] = $tag;
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $return;
    }

    public static function get(string $tags, string ...$field): array
    {
        $parse = static::parse($tags);
        $db = DB::getInstance();
        $tagsTable = DB::getTable('tags');
        $binds = [];
        $inSQL = [];
        foreach ($parse as $pos => $tag) {
            $binds[':tag_' . $pos] = $tag;
            $inSQL[] = ':tag_' . $pos;
        }
        $inSQL = implode(',', $inSQL);
        $selectTpl = <<<MySQL
        `tag_%s` %s
        MySQL;
        if ($field === []) {
            $field = ['id', 'name'];
        }
        if (! in_array('name', $field)) {
            $field[] = 'name';
        }
        $select = [];
        foreach ($field as $column) {
            $select[] = strtr($selectTpl, [
                '%s' => $column,
            ]);
        }
        $select = implode(',', $select);
        $query = <<<SQL
        SELECT {$select}
        FROM `{$tagsTable}`
        WHERE `tag_name` IN ({$inSQL})
        ORDER BY `tag_name` COLLATE utf8mb4_general_ci ASC;
        SQL;
        $db->query($query);
        foreach ($binds as $pos => $v) {
            $db->bind($pos, $v);
        }

        return $db->fetchAll() ?: [];
    }

    public static function autocomplete(string $try, int $limit = 10): array
    {
        $limit = max(1, $limit);
        $db = DB::getInstance();
        $tagsTable = DB::getTable('tags');
        $query = <<<SQL
        SELECT `tag_name` name
        FROM `{$tagsTable}`
        WHERE `tag_name` LIKE :try COLLATE utf8mb4_general_ci
        ORDER BY `tag_name` COLLATE utf8mb4_general_ci ASC
        LIMIT {$limit};

        SQL;
        $db->query($query);
        $db->bind(':try', $try . '%');

        return $db->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public static function insert(int $user_id, string ...$tag): void
    {
        if ($tag === []) {
            return;
        }
        $maxTags = (int) env()['CHEVERETO_MAX_TAGS'];
        if ($maxTags > 0) {
            $currentTotalTags = Stat::getTotals()['tags'] ?? 0;
            if ($currentTotalTags >= $maxTags) {
                return;
            }
            $count = count($tag);
            $newTotalTags = $currentTotalTags + $count;
            if ($newTotalTags > $maxTags) {
                $excessTags = $newTotalTags - $maxTags;
                if ($excessTags > 0) {
                    $tag = array_slice($tag, 0, $count - $excessTags);
                }
            }
        }
        if ($tag === []) {
            return;
        }
        $tagsTable = DB::getTable('tags');
        $statsTable = DB::getTable('stats');
        $sql = '';
        $binds = [
            ':tag_user_id' => $user_id,
        ];
        $template = <<<SQL
        SET @TRY_TAG = :tag_name_%;
        SET @MISSING = IF(
            EXISTS(
                SELECT `tag_id` FROM `{$tagsTable}` WHERE `tag_name` = @TRY_TAG
            ),
            NULL,
            @TRY_TAG
        );
        INSERT INTO `{$tagsTable}` (`tag_name`, `tag_user_id`)
        SELECT @MISSING, :tag_user_id
        WHERE @MISSING IS NOT NULL;

        UPDATE `{$statsTable}`
        SET stat_tags = stat_tags + 1
        WHERE stat_type = "total"
        AND @MISSING IS NOT NULL;

        INSERT INTO `{$statsTable}` (stat_type, stat_date_gmt, stat_tags)
        SELECT "date", DATE(CURRENT_TIMESTAMP), 1
        WHERE @MISSING IS NOT NULL
        ON DUPLICATE KEY UPDATE stat_tags = stat_tags + 1;

        SQL;
        foreach ($tag as $pos => $name) {
            static::assert($name);
            $sql .= str_replace('%', $pos, $template);
            $binds[':tag_name_' . $pos] = $name;
        }
        $db = DB::getInstance();
        $db->query($sql);
        foreach ($binds as $key => $value) {
            $db->bind($key, $value);
        }
        if (! $db->exec()) {
            throw new LogicException('Failed to insert tags');
        }
    }

    /**
     * @param string $name Non-encoded name!
     */
    public static function row(string $name, string $url = 'tag/%s'): array
    {
        $url_key = rawurlencode($name);

        return [
            'name' => $name,
            'name_safe_html' => safe_html($name),
            'url_key' => $url_key,
            'url' => get_base_url(
                strtr($url, [
                    '%s' => $url_key,
                ])
            ),
        ];
    }

    public static function addUrlFilters(
        array &$tag,
        string $base_url,
        array $tags_active
    ): void {
        $tag['url_append'] = '';
        $tag['url_remove'] = '';
        $base_url_tag_filter = $base_url . '/?tag=';
        if (in_array($tag['name'], $tags_active)) {
            $pos = array_search($tag['name'], $tags_active);
            unset($tags_active[$pos]);
            $tag['url_remove'] = $base_url_tag_filter
                . rawurlencode(implode(',', $tags_active));
            if ($tags_active === []) {
                $tag['url_remove'] = $base_url;
            }
        } else {
            $tags_active[] = $tag['name'];
            asort($tags_active);
            $tag['url_append'] = $base_url_tag_filter
                . rawurlencode(implode(',', $tags_active));
        }
    }

    public static function update(int|string $id, array $values): int|false
    {
        if (isset($values['name'])) {
            static::assert($values['name']);
        }
        if (isset($values['description'])) {
            assertNotStopWords($values['description']);
        }
        $noEdit = ['files', 'views', 'id', 'date_gmt'];
        foreach ($noEdit as $key) {
            if (isset($values[$key])) {
                unset($values[$key]);
            }
        }

        return DB::update('tags', $values, [
            'id' => $id,
        ]);
    }

    public static function delete(int ...$id): bool
    {
        $tagsTable = DB::getTable('tags');
        $statsTable = DB::getTable('stats');
        $db = DB::getInstance();
        $sql = <<<SQL
        SET @DELETE_COUNT = 0;

        SQL;
        foreach ($id as $tagId) {
            $sql .= <<<SQL
            SET @DATE = (SELECT DATE(`tag_date_gmt`) FROM `{$tagsTable}` WHERE `tag_id` = {$tagId});

            DELETE FROM `{$tagsTable}`
            WHERE `tag_id` = {$tagId}
            AND @DATE IS NOT NULL;

            SET @ROW_COUNT = ROW_COUNT();
            SET @DELETE_COUNT = @DELETE_COUNT + @ROW_COUNT;

            UPDATE `{$statsTable}`
            SET stat_tags = GREATEST(GREATEST(0, stat_tags) - @ROW_COUNT, 0)
            WHERE stat_type = "date"
            AND stat_date_gmt = @DATE
            AND @DATE IS NOT NULL;

            SQL;
        }
        $sql .= <<<SQL
        UPDATE `{$statsTable}` SET stat_tags = GREATEST(GREATEST(0, stat_tags) - @DELETE_COUNT, 0)
        WHERE stat_type = "total";

        SQL;
        $db->query($sql);

        $return = $db->exec();
        if ($return) {
            Listing::deleteTypeIdCache('t', ...$id);
        }

        return $return;
    }

    public static function formatArray(array $object): array
    {
        if ($object !== []) {
            $output = DB::formatRow($object);
            self::fill($output);

            return $output;
        }

        return $object;
    }

    public static function fill(array &$tag): void
    {
        $tag = array_merge($tag, static::row($tag['name']));
    }
}
