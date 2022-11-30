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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use Chevere\Throwable\Exceptions\OverflowException;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Vars\env;
use DateTime;
use Exception;

class Stat
{
    public static function getTotals(): array
    {
        $res = DB::queryFetchSingle('SELECT * FROM ' . DB::getTable('stats') . ' WHERE stat_type = "total"');
        unset($res['stat_id'], $res['stat_type'], $res['date_gmt']);

        return DB::formatRow($res, 'stat');
    }

    public static function getDaily(): array
    {
        $res = DB::queryFetchAll('SELECT * FROM ' . DB::getTable('stats') . ' WHERE stat_type = "date" ORDER BY stat_date_gmt DESC LIMIT 365');
        $res = DB::formatRows($res, 'stat');

        return array_reverse($res);
    }

    public static function getByDateCumulative(): array
    {
        $res = static::getDaily();
        $return = [];
        $cumulative = [
            'users' => 0,
            'images' => 0,
            'albums' => 0,
            'image_views' => 0,
            'album_views' => 0,
            'image_likes' => 0,
            'disk_used' => 0,
        ];
        foreach ($res as $k => $v) {
            foreach ($cumulative as $col => &$sum) {
                $sum += $v[$col];
                $v[$col . '_acc'] = $sum;
            }
            $return[$v['date_gmt']] = $v;
        }

        return $return;
    }

    public static function assertMax(string $type): void
    {
        if (!in_array($type, ['images', 'albums', 'users'])) {
            throw new LogicException(
                message('Invalid stat type: %s')
                    ->withCode('%s', $type),
                600
            );
        }
        $maxLimit = (int) env()['CHEVERETO_MAX_' . strtoupper($type)];
        if ($maxLimit > 0) {
            $count = Stat::getTotals()[$type] ?? 0;
            if (($count + 1) > $maxLimit) {
                throw new OverflowException(
                    message('Max %t reached (limit %s)')
                        ->withStrtr('%t', $type)
                        ->withStrtr('%s', strval($maxLimit)),
                    999
                );
            }
        }
    }

    public static function rebuildTotals(): void
    {
        $query = 'TRUNCATE TABLE `%table_prefix%stats`;
        INSERT INTO `%table_prefix%stats` (stat_id, stat_date_gmt, stat_type) VALUES ("1", NULL, "total") ON DUPLICATE KEY UPDATE stat_type=stat_type;
        UPDATE `%table_prefix%stats` SET
        stat_images = (SELECT IFNULL(COUNT(*),0) FROM `%table_prefix%images`),
        stat_albums = (SELECT IFNULL(COUNT(*),0) FROM `%table_prefix%albums`),
        stat_users = (SELECT IFNULL(COUNT(*),0) FROM `%table_prefix%users`),
        stat_image_views = (SELECT IFNULL(SUM(image_views),0) FROM `%table_prefix%images`),
        stat_disk_used = (SELECT IFNULL(SUM(image_size) + SUM(image_thumb_size) + SUM(image_medium_size),0) FROM `%table_prefix%images`)
        WHERE stat_type = "total";
        INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_images, stat_image_views, stat_disk_used)
        SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_images, sb.stat_image_views, sb.stat_disk_used
        FROM (SELECT "date" AS stat_type, DATE(image_date_gmt) AS stat_date_gmt, COUNT(*) AS stat_images, SUM(image_views) AS stat_image_views, SUM(image_size + image_thumb_size + image_medium_size) AS stat_disk_used FROM `%table_prefix%images` GROUP BY DATE(image_date_gmt)) AS sb
        ON DUPLICATE KEY UPDATE stat_images = sb.stat_images;
        INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_users)
        SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_users
        FROM (SELECT "date" AS stat_type, DATE(user_date_gmt) AS stat_date_gmt, COUNT(*) AS stat_users FROM `%table_prefix%users` GROUP BY DATE(user_date_gmt)) AS sb
        ON DUPLICATE KEY UPDATE stat_users = sb.stat_users;
        INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_albums)
        SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_albums
        FROM (SELECT "date" AS stat_type, DATE(album_date_gmt) AS stat_date_gmt, COUNT(*) AS stat_albums FROM `%table_prefix%albums` GROUP BY DATE(album_date_gmt)) AS sb
        ON DUPLICATE KEY UPDATE stat_albums = sb.stat_albums;
        UPDATE `%table_prefix%users` SET user_content_views = COALESCE((SELECT SUM(image_views) FROM `%table_prefix%images` WHERE image_user_id = user_id GROUP BY user_id), "0");';
        $sql = strtr($query, [
            '%table_prefix%' => env()['CHEVERETO_DB_TABLE_PREFIX'],
        ]);
        $db = DB::getInstance();
        $db->query($sql);
        $db->exec();
    }

    public static function track(array $args = []): void
    {
        if (!in_array($args['action'], ['insert', 'update', 'delete'])) {
            throw new Exception(sprintf('Invalid stat action "%s" in ', $args['action']), 600);
        }
        $tables = DB::getTables();
        if (!array_key_exists($args['table'], $tables)) {
            throw new Exception(sprintf('Unknown table "%s"', $args['table']), 601);
        }
        if ($args['action'] === 'insert' && !in_array($args['table'], ['albums', 'images', 'likes', 'users'])) {
            throw new Exception(sprintf('Table "%s" does not bind an stat procedure', $args['table']), 601);
        }
        if ($args['table'] == 'images' && in_array($args['action'], ['insert', 'delete'])) {
            if (!isset($args['disk_sum'])) {
                $disk_sum_value = 0;
            } elseif (preg_match('/^([\+\-]{1})?\s*([\d]+)$/', (string) $args['disk_sum'], $matches)) {
                $disk_sum_value = $matches[2];
            } else {
                throw new Exception(sprintf('Invalid disk_sum value "%s"', $args['disk_sum']), 604);
            }
        }
        if (!isset($args['value'])) {
            $value = 1;
        } elseif (preg_match('/^([\+\-]{1})?\s*([\d]+)$/', (string) $args['value'], $matches)) {
            $value = $matches[2];
        } else {
            throw new Exception(sprintf('Invalid value "%s"', $args['value']), 602);
        }
        if (!isset($args['date_gmt'])) {
            switch ($args['action']) {
                case 'insert':
                case 'update':
                    $args['date_gmt'] = datetimegmt();

                break;
                case 'delete':
                    throw new Exception('Missing date_gmt value', 605);
            }
        } else {
            $date = new DateTime($args['date_gmt']);
            $args['date_gmt'] = $date->format('Y-m-d');
        }
        $sql_tpl = '';
        switch ($args['action']) {
            case 'insert':
                switch ($args['table']) {
                    case 'images':
                        if (!isset($args['disk_sum'])) {
                            throw new Exception('Missing disk_sum value', 603);
                        }
                        $sql_tpl =
                            'UPDATE `%table_stats` SET stat_images = stat_images + %value, stat_disk_used = stat_disk_used + %disk_sum WHERE stat_type = "total";'
                            . "\n"
                            . 'INSERT INTO `%table_stats` (stat_type, stat_date_gmt, stat_images, stat_disk_used) VALUES ("date",DATE("%date_gmt"),"%value", "%disk_sum") ON DUPLICATE KEY UPDATE stat_images = stat_images + %value, stat_disk_used = stat_disk_used + %disk_sum;';

                    break;
                    default: // albums, likes, users
                        $sql_tpl =
                            'UPDATE `%table_stats` SET stat_%related_table = stat_%related_table + %value WHERE stat_type = "total";'
                            . "\n"
                            . 'INSERT `%table_stats` (stat_type, stat_date_gmt, stat_%related_table) VALUES ("date",DATE("%date_gmt"),"%value") ON DUPLICATE KEY UPDATE stat_%related_table = stat_%related_table + %value;';

                    break;
                }

            break;

            case 'update':
                switch ($args['table']) {
                    case 'images':
                    case 'albums':
                        // Track image | album | user views
                        $sql_tpl =
                            'UPDATE `%table_stats` SET stat_%aux_views = stat_%aux_views + %value WHERE stat_type = "total";'
                            . "\n"
                            . 'INSERT INTO `%table_stats` (stat_type, stat_date_gmt, stat_%aux_views) VALUES ("date",DATE("%date_gmt"),"%value") ON DUPLICATE KEY UPDATE stat_%aux_views = stat_%aux_views + %value;';
                        if (isset($args['user_id'])) {
                            $sql_tpl .= "\n" . 'UPDATE `%table_users` SET user_content_views = user_content_views + %value WHERE user_id = %user_id;';
                        }
                        $sql_tpl = strtr($sql_tpl, ['%aux' => DB::getFieldPrefix($args['table'])]);

                    break;
                }

            break;

            case 'delete':
                switch ($args['table']) {
                    case 'images':
                        $sql_tpl =
                            'UPDATE `%table_stats` SET stat_images = GREATEST(stat_images - %value, 0) WHERE stat_type = "total";'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_images = GREATEST(stat_images - %value, 0) WHERE stat_type = "date" AND stat_date_gmt = DATE("%date_gmt");'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_image_likes = GREATEST(stat_image_likes - %likes, 0) WHERE stat_type = "total";'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_image_likes = GREATEST(stat_image_likes - %likes, 0) WHERE stat_type = "date" AND stat_date_gmt = DATE("%date_gmt");'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_disk_used = GREATEST(stat_disk_used - %disk_sum, 0) WHERE stat_type = "total";'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_disk_used = GREATEST(stat_disk_used - %disk_sum, 0) WHERE stat_type = "date" AND stat_date_gmt = DATE("%date_gmt");';

                    break;
                    default:  // albums, likes, users
                        $sql_tpl =
                            'UPDATE `%table_stats` SET stat_%related_table = GREATEST(stat_%related_table - %value, 0) WHERE stat_type = "total";'
                            . "\n"
                            . 'UPDATE `%table_stats` SET stat_%related_table = GREATEST(stat_%related_table - %value, 0) WHERE stat_type = "date" AND stat_date_gmt = DATE("%date_gmt");';
                        if ($args['table'] == 'users') {
                            $sql_tpl .=
                                // Update likes stats related to this deleted user
                                'UPDATE IGNORE `%table_stats` AS S
									INNER JOIN (
										SELECT DATE(like_date_gmt) AS like_date_gmt, COUNT(*) AS cnt
										FROM `%table_likes`
											WHERE like_user_id = %user_id
										GROUP BY DATE(like_date_gmt)
								  ) AS L ON S.stat_date_gmt = L.like_date_gmt
								SET S.stat_image_likes = GREATEST(S.stat_image_likes - COALESCE(L.cnt, "0"), 0) WHERE stat_type = "date";
								UPDATE IGNORE `%table_stats` SET stat_image_likes = GREATEST(stat_image_likes - COALESCE((SELECT COUNT(*) FROM `%table_likes` WHERE like_user_id = %user_id), "0"), 0) WHERE stat_type = "total";'
                                . "\n"
                                // Update album stats related to this deleted user
                                . 'UPDATE IGNORE `%table_stats` AS S
									INNER JOIN (
										SELECT DATE(album_date_gmt) AS album_date_gmt, COUNT(*) AS cnt
										FROM `%table_albums`
											WHERE album_user_id = %user_id
										GROUP BY DATE(album_date_gmt)
								  ) AS A ON S.stat_date_gmt = A.album_date_gmt
								SET S.stat_albums = GREATEST(S.stat_albums - COALESCE(A.cnt, "0"), 0) WHERE stat_type = "date";
								UPDATE IGNORE `%table_stats` SET stat_albums = GREATEST(stat_albums - COALESCE((SELECT COUNT(*) FROM `%table_albums` WHERE album_user_id = %user_id), "0"), 0) WHERE stat_type = "total";';
                        }

                    break;
                }

            break;
        }
        if ($sql_tpl === '') {
            throw new LogicException();
        }
        $sql = strtr($sql_tpl, [
            '%table_stats' => $tables['stats'],
            '%table_users' => $tables['users'],
            '%table_likes' => $tables['likes'],
            '%table_albums' => $tables['albums'],
            '%related_table' => (isset($args['content_type']) ? ($args['content_type'] . '_') : null) . $args['table'],
            '%value' => $value,
            '%date_gmt' => $args['date_gmt'],
            '%user_id' => $args['user_id'] ?? 0,
            '%disk_sum' => $disk_sum_value ?? 0,
            '%likes' => $args['likes'] ?? 0,
        ]);
        DB::queryExecute($sql);
    }
}
