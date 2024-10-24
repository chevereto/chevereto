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

use BadMethodCallException;
use Chevereto\Legacy\G\Handler;
use DateTime;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\ends_with;
use function Chevereto\Legacy\G\forward_slash;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\G\require_theme_file_return;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\missing_values_to_exception;
use function Chevereto\Vars\env;
use function Chevereto\Vars\request;

class Listing
{
    public string $query;

    public array $seek;

    public string $seekEnd = '';

    public string $seekStart = '';

    public int $count = 0;

    public bool $nsfw;

    public static array $valid_types = ['images', 'albums', 'users', 'tags'];

    public static array $valid_sort_types = ['date_gmt', 'size', 'views', 'id', 'image_count', 'name', 'title', 'username'];

    public array $output = [];

    private int $offset;

    private array $params_hidden;

    private int $limit;

    private string $sort_type;

    private string $sort_order;

    private ?int $owner = null;

    private array $requester = [];

    private $privacy;

    private int $output_count = 0;

    private bool $has_page_next;

    private array $output_assoc = [];

    private bool $sfw = true;

    private bool $has_page_prev;

    private int $isApproved = 1;

    private array $binds = [];

    private string $type;

    private int $category;

    /**
     * @var int[] Tags ids
     */
    private array $tagsIds = [];

    /**
     * @var string Tags names for ?tag=
     */
    private string $tagsString = '';

    private string $tagsMatch = 'any';

    private string $where = '';

    private array|bool $tools = false;

    private bool $reverse = false;

    private ?string $outputTpl;

    public function outputCount(): int
    {
        return $this->output_count;
    }

    public function outputAssoc(): array
    {
        return $this->output_assoc;
    }

    public function setOutputTpl(string $tpl): void
    {
        $this->outputTpl = $tpl;
    }

    public function outputTpl(): ?string
    {
        return $this->outputTpl ?? null;
    }

    public function debugQuery()
    {
        if (! isset($this->query)) {
            throw new BadMethodCallException();
        }
        $params = [];
        foreach ($this->binds as $bind) {
            $params[] = $bind['param'] . '=' . $bind['value'];
        }

        return '# Dumped listing query'
            . "\n" . $this->query
            . "\n\n# Dumped query params"
            . "\n" . implode("\n", $params);
    }

    public function limit(): int
    {
        return $this->limit;
    }

    // Sets the `image_is_approved` flag
    public function setApproved($bool)
    {
        $this->isApproved = $bool;
    }

    // Sets the type of resource being listed
    public function setType($type)
    {
        $this->type = $type === 'files'
            ? 'images'
            : $type;
    }

    // Sets the offset (sql> LIMIT offset,limit)
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
    }

    public function sfw(): bool
    {
        return $this->sfw;
    }

    public function has_page_prev(): bool
    {
        return $this->has_page_prev;
    }

    public function has_page_next(): bool
    {
        return $this->has_page_next;
    }

    // Sets ID to seek next-to
    public function setSeek(string $seek)
    {
        if (strpos($seek, '.') !== false) {
            $explode = explode('.', $seek);
            $copy = $explode;
            end($explode);
            $last = key($explode);
            unset($copy[$last]);
            $array = [
                0 => implode('.', $copy),
                1 => decodeID($explode[$last]),
            ];
            $this->seek = $array;

            return;
        }
        $decodeID = decodeID($seek);
        if (ctype_digit(strval($decodeID))) {
            $this->seek = ['0000-01-01 00:00:00', $decodeID];
        }
    }

    public function setReverse($bool): void
    {
        $this->reverse = $bool;
    }

    public function setParamsHidden($params): void
    {
        $this->params_hidden = $params;
    }

    // Sets the limit (sql> LIMIT offset,limit)
    public function setLimit($limit): void
    {
        $this->limit = (int) $limit;
    }

    // Sets the sort type (sql> SORT BY sort_type)
    public function setSortType($sort_type): void
    {
        $this->sort_type = $sort_type === 'date' ? 'date_gmt' : $sort_type;
    }

    // Sets the sort order (sql> DESC | ASC)
    public function setSortOrder($sort_order): void
    {
        $this->sort_order = $sort_order;
    }

    // Sets the WHERE clause
    public function setWhere(string $where): void
    {
        $this->where = $where;
    }

    public function setOwner(int $user_id): void
    {
        $this->owner = $user_id;
    }

    public function setRequester(array $user): void
    {
        $this->requester = $user;
    }

    public function setCategory($category): void
    {
        $this->category = (int) $category;
    }

    public function setTagsIds(int ...$id): void
    {
        $this->tagsIds = $id;
        $this->tagsString = '';
    }

    public function setTagsString(string $tags): void
    {
        $this->tagsString = $tags;
    }

    public function setTagsMatch(string $operator): void
    {
        if (! in_array($operator, ['any', 'all'])) {
            throw new Exception('Invalid tags operator');
        }
        $this->tagsMatch = $operator;
    }

    public function setPrivacy($privacy): void
    {
        $this->privacy = $privacy;
    }

    public function setTools(array|bool $flag): void
    {
        $this->tools = $flag;
    }

    public function bind($param, $value, $type = null)
    {
        $this->binds[] = [
            'param' => $param,
            'value' => $value,
            'type' => $type,
        ];
    }

    /**
     * Do the thing
     * @Exeption 4xx
     */
    public function exec()
    {
        $this->validateInput();
        $tables = DB::getTables();
        $emptyTypeClauses = [];
        if ($this->requester === []) {
            $this->setRequester(Login::getUser());
        }
        if ($this->type === 'images') {
            $this->where = $this->getWhere('image_is_approved = ' . (int) $this->isApproved);
        }
        if (! (bool) env()['CHEVERETO_ENABLE_USERS']) {
            $userId = getSetting('website_mode_personal_uid') ?? 0;
            if ($userId !== 0) {
                $this->where = match ($this->type) {
                    'images' => $this->getWhere('image_user_id=' . $userId),
                    'albums' => $this->getWhere('album_user_id=' . $userId),
                    default => $this->where
                };
            }
        }
        $joins = [
            'images' => [
                'storages' => 'LEFT JOIN ' . $tables['storages'] . ' ON ' . $tables['images'] . '.image_storage_id = ' . $tables['storages'] . '.storage_id',
                'users' => 'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['images'] . '.image_user_id = ' . $tables['users'] . '.user_id',
                'albums' => 'LEFT JOIN ' . $tables['albums'] . ' ON ' . $tables['images'] . '.image_album_id = ' . $tables['albums'] . '.album_id',
                'categories' => 'LEFT JOIN ' . $tables['categories'] . ' ON ' . $tables['images'] . '.image_category_id = ' . $tables['categories'] . '.category_id',
            ],
            'users' => [],
            'albums' => [
                'users' => 'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['albums'] . '.album_user_id = ' . $tables['users'] . '.user_id',
            ],
            'tags' => [],
        ];
        if ($this->type === 'users' && $this->sort_type === 'views') {
            $this->sort_type = 'content_views';
        }
        if (isset($this->params_hidden)) {
            $emptyTypeClauses['users'][] = 'user_image_count > 0 OR user_avatar_filename IS NOT NULL OR user_background_filename IS NOT NULL';
            if ($this->sort_type === 'views') {
                $emptyTypeClauses['albums'][] = 'album_views > 0';
                $emptyTypeClauses['images'][] = 'image_views > 0';
                $emptyTypeClauses['users'][] = 'user_content_views > 0';
                $emptyTypeClauses['tags'][] = 'tag_views > 0';
            }
            if ($this->sort_type === 'likes') {
                $emptyTypeClauses['albums'][] = 'album_likes > 0';
                $emptyTypeClauses['images'][] = 'image_likes > 0';
                $emptyTypeClauses['users'][] = 'user_likes > 0';
            }
            if ($this->type === 'albums') {
                if (isset($this->params_hidden['album_min_image_count']) && $this->params_hidden['album_min_image_count'] > 0) {
                    $whereClauses[] = sprintf('album_image_count >= %d', $this->params_hidden['album_min_image_count']);
                } else {
                    $emptyTypeClauses['albums'][] = 'album_image_count > 0';
                }
            }
            if ($this->type === 'tags') {
                $emptyTypeClauses['tags'][] = 'tag_files > 0';
            }
            if (array_key_exists($this->type, $emptyTypeClauses)
                && isset($this->params_hidden['hide_empty']) && $this->params_hidden['hide_empty'] == 1
            ) {
                $whereClauses[] = '(' . implode(') AND (', $emptyTypeClauses[$this->type]) . ')';
            }
            if ($this->type !== 'tags'
                && isset($this->params_hidden['hide_banned'])
                && $this->params_hidden['hide_banned'] == 1
            ) {
                $whereClauses[] = '(' . $tables['users'] . '.user_status IS NULL OR ' . $tables['users'] . '.user_status <> "banned"' . ')';
            }
            if ($this->type === 'images') {
                if (($this->params_hidden['is_animated'] ?? 0) == 1) {
                    $whereClauses[] = 'image_is_animated = 1';
                }
                if (($this->params_hidden['is_video'] ?? 0) == 1) {
                    $whereClauses[] = 'image_type = 2';
                }
                if (($this->params_hidden['is_image'] ?? 0) == 1) {
                    $whereClauses[] = 'image_type = 1';
                }
            }
            if (! empty($whereClauses)) {
                $whereClauses = implode(' AND ', $whereClauses);
                $this->where = $this->getWhere($whereClauses);
            }
        }
        $type_singular = DB::getFieldPrefix($this->type);
        if ($this->where !== '') {
            $where_clauses = explode(' ', str_ireplace('WHERE ', '', $this->where));
            $where_arr = [];
            foreach ($where_clauses as $clause) {
                if (! preg_match('/\./', $clause)) {
                    $field_prefix = explode('_', $clause, 2)[0]; // field prefix (singular)
                    $table = DB::getTableFromFieldPrefix($field_prefix); // image -> chv_images
                    $table_prefix = env()['CHEVERETO_DB_TABLE_PREFIX'];
                    $table_key = empty($table_prefix) ? $table : str_replace_first($table_prefix, '', $table);
                    $where_arr[] = array_key_exists($table_key, $tables) ? $table . '.' . $clause : $clause;
                } else {
                    $where_arr[] = $clause; // Let it be
                }
            }
            $this->where = 'WHERE ' . implode(' ', $where_arr);
        }
        if (version_compare(cheveretoVersionInstalled(), '3.7.0', '>=')) {
            // Dynamic since v3.9.0
            $likes_join = 'LEFT JOIN '
                . $tables['likes']
                . ' ON '
                . $tables['likes']
                . '.like_content_type = "'
                . $type_singular
                . '" AND '
                . $tables['likes']
                . '.like_content_id = '
                . $tables[$this->type]
                . '.'
                . $type_singular
                . '_id';
            if (preg_match('/like_user_id/', $this->where)) {
                $joins[$this->type]['likes'] = $likes_join;
            } elseif ($this->requester !== [] && ! in_array($this->type, ['users', 'tags'])) {
                $joins[$this->type]['likes'] = $likes_join
                    . ' AND '
                    . $tables['likes']
                    . '.like_user_id = '
                    . $this->requester['id'];
            }
            $follow_tpl_join = 'LEFT JOIN '
                . $tables['follows']
                . ' ON '
                . $tables['follows']
                . '.%FIELD = '
                . $tables[$this->type]
                . '.'
                . ($this->type === 'users' ? 'user' : DB::getFieldPrefix($this->type) . '_user')
                . '_id';
            if (preg_match('/follow_user_id/', $this->where)) {
                $joins[$this->type]['follows'] = strtr($follow_tpl_join, [
                    '%FIELD' => 'follow_followed_user_id',
                ]);
            }
            if (preg_match('/follow_followed_user_id/', $this->where)) {
                $joins[$this->type]['follows'] = strtr($follow_tpl_join, [
                    '%FIELD' => 'follow_user_id',
                ]);
            }
        }
        // Add ID reservation clause
        if ($this->type === 'images') {
            $res_id_where = 'image_size > 0';
            if ($this->where === '') {
                $this->where = 'WHERE ' . $res_id_where;
            } else {
                $this->where .= ' AND ' . $res_id_where;
            }
            // Add category clause
            if (isset($this->category)) {
                $category_qry = $tables['images'] . '.image_category_id = ' . $this->category;
                if ($this->where === '') {
                    $this->where = 'WHERE ' . $category_qry;
                } else {
                    $this->where .= ' AND ' . $category_qry;
                }
            }
        }
        if (in_array($this->type, ['images', 'albums'])) {
            if (isset($this->params_hidden['tag_id'])) {
                $tag_param_ids = explode(',', $this->params_hidden['tag_id']);
                $tag_param_ids = array_map(function ($id): int {
                    return decodeID($id);
                }, $tag_param_ids);
                $this->tagsIds = array_merge(
                    $this->tagsIds,
                    $tag_param_ids
                );
                $this->tagsIds = array_unique($this->tagsIds);
            }
            if (isset($this->params_hidden['tag_match'])) {
                $this->setTagsMatch($this->params_hidden['tag_match']);
            }
            if ($this->tagsIds !== []) {
                $tagBinds = [];
                foreach ($this->tagsIds as $k => $tag) {
                    $tagBinds[':tag_' . $k] = $tag;
                }
                $inTagsSQL = implode(',', array_keys($tagBinds));
                $tableTagsFiles = $tables['tags_files'];
                $relationTagIdColumn = 'tag_file_tag_id';
                $tableImages = $tables['images'];
                $tagSql = <<<MySQL
                EXISTS (
                    SELECT *
                    FROM `{$tableTagsFiles}`
                    WHERE `{$relationTagIdColumn}` IN ({$inTagsSQL})
                    AND `tag_file_file_id` = {$tableImages}.image_id

                MySQL;
                if ($this->type === 'albums') {
                    $tableRelation = $tables['tags_albums'];
                    $relationTagIdColumn = 'tag_album_tag_id';
                    $tableAlbums = $tables['albums'];
                    $tagSql = <<<MySQL
                    EXISTS (
                        SELECT *
                        FROM `{$tableRelation}`
                        WHERE `{$relationTagIdColumn}` IN ({$inTagsSQL})
                        AND `tag_album_album_id` = {$tableAlbums}.album_id
                        AND `tag_album_count` > 0

                    MySQL;
                }
                if ($this->tagsMatch === 'all') {
                    $tagBinds[':tag_count'] = count($this->tagsIds);
                    $tagSql .= " HAVING COUNT(`{$relationTagIdColumn}`) = :tag_count";
                }
                $tagSql .= ')';
                if ($this->where === '') {
                    $this->where = 'WHERE ' . $tagSql;
                } else {
                    $this->where .= ' AND ' . $tagSql;
                }
                foreach ($tagBinds as $k => $v) {
                    $this->bind($k, $v);
                }
            }
        }
        // Privacy layer
        if (
            ! ($this->requester['is_admin'] ?? false)
            && in_array($this->type, ['images', 'albums', 'users'], true)
            && (
                (! isset($this->owner) || $this->requester === []) || $this->owner !== $this->requester['id']
            )
        ) {
            if ($this->where === '') {
                $this->where = 'WHERE ';
            } else {
                $this->where .= ' AND ';
            }
            $nsfw_off = $this->requester !== []
                ? ! $this->requester['show_nsfw_listings']
                : ! getSetting('show_nsfw_in_listings');
            switch ($this->type) {
                case 'images':
                    if ($nsfw_off) {
                        $nsfw_off_clause = $tables['images'] . '.image_nsfw = 0';
                        if ($this->requester !== []) {
                            $this->where .= '(' . $nsfw_off_clause . ' OR (' . $tables['images'] . '.image_nsfw = 1 AND ' . $tables['images'] . '.image_user_id = ' . $this->requester['id'] . ')) AND ';
                        } else {
                            $this->where .= $nsfw_off_clause . ' AND ';
                        }
                    }

                    break;
                case 'users':
                    $this->where .= $tables['users'] . '.user_is_private = 0';

                    break;
            }
            if ($this->type !== 'users') {
                if (getSetting('website_privacy_mode') === 'public' || $this->privacy === 'private_but_link' || getSetting('website_content_privacy_mode') === 'default') {
                    $this->where .= '(' . $tables['albums'] . '.album_privacy NOT IN';
                    $privacy_modes = ['private', 'private_but_link', 'custom'];
                    if ($this->type === 'images') {
                        $privacy_modes[] = 'password';
                    }
                    if (isset($this->privacy)
                        && in_array($this->privacy, $privacy_modes, true)
                    ) {
                        unset($privacy_modes[array_search($this->privacy, $privacy_modes, true)]);
                    }
                    $this->where .= ' (' . "'" . implode("','", $privacy_modes) . "'" . ') ';
                    $this->where .= 'OR ' . $tables['albums'] . '.album_privacy IS NULL';
                    if ($this->requester !== []) {
                        $this->where .= ' OR ' . $tables['albums'] . '.album_user_id =' . $this->requester['id'];
                    }
                    $this->where .= ')';
                } else {
                    $injected_requester = $this->requester['id'] ?? '0';
                    $this->where .= '(' . $tables['albums'] . '.album_user_id = ' . $injected_requester;
                    $this->where .= $this->type === 'albums' ? ')' : (' OR ' . $tables['images'] . '.image_user_id = ' . $injected_requester . ')');
                }
            }
        }
        $sort_field = $type_singular . '_' . $this->sort_type;
        $key_field = $type_singular . '_id';
        if (isset($this->seek)) {
            if (ends_with('date_gmt', $this->sort_type)) {
                $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->seek[0]);
                if (! $d || $d->format('Y-m-d H:i:s') !== $this->seek[0]) {
                    $this->seek = ['0000-01-01 00:00:00', $this->seek[1]];
                }
            }
            if ($this->where === '') {
                $this->where = 'WHERE ';
            } else {
                $this->where .= ' AND ';
            }
            if ($this->reverse) {
                $this->sort_order = $this->sort_order === 'asc' ? 'desc' : 'asc';
            }
            $signo = $this->sort_order === 'desc' ? '<=' : '>=';
            if ($this->sort_type === 'id') {
                $this->where .= $sort_field . ' ' . $signo . ' :seek';
                $this->bind(':seek', $this->seek);
            } else {
                $signo = $this->sort_order === 'desc' ? '<' : '>';
                $this->where .= '((' . $sort_field . ' ' . $signo . ' :seekSort) OR (' . $sort_field . ' = :seekSort AND ' . $key_field . ' ' . $signo . '= :seekKey))';
                $this->bind(':seekSort', $this->seek[0]);
                $this->bind(':seekKey', $this->seek[1]);
            }
        }
        if ($this->where !== '') {
            $this->where = "\n" . $this->where;
        }
        $sort_order = strtoupper($this->sort_order);
        $table_order = DB::getTableFromFieldPrefix($type_singular);
        $order_by = "\n" . 'ORDER BY ';
        if (in_array($this->sort_type, ['name', 'title', 'username'], true)) {
            $order_by .= 'CAST(' . $table_order . '.' . $sort_field . ' as CHAR) ' . $sort_order . ', ';
            $order_by .= 'LENGTH(' . $table_order . '.' . $sort_field . ') ' . $sort_order . ', ';
        }
        $order_by .= '' . $table_order . '.' . $sort_field . ' ' . $sort_order;
        if ($this->sort_type !== 'id') {
            $order_by .= ', ' . $table_order . '.' . $key_field . ' ' . $sort_order;
        }
        $limit = '';
        if ($this->limit > 0) {
            $limit = "\n" . 'LIMIT ' . ($this->limit + 1); // +1 allows to fetch "one extra" to detect prev/next pages
        }
        $base_table = $tables[$this->type];
        // Normal query
        if (empty($joins[$this->type])) {
            $query = 'SELECT * FROM ' . $base_table;
            $query .= $this->where . $order_by . $limit;
            // Alternative query
        } else {
            if ($this->where !== '') {
                preg_match_all('/' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '([\w_]+)\./', $this->where, $where_tables);
                $where_tables = array_values(array_diff(array_unique($where_tables[1]), [$this->type]));
            } else {
                $where_tables = false;
            }
            if ($where_tables !== []) {
                $join_tables = $where_tables;
            } else {
                reset($joins);
                $join_tables = [key($joins)];
            }
            $join = '';
            if (is_iterable($join_tables)) {
                foreach ($join_tables as $join_table) {
                    if (! empty($joins[$this->type][$join_table])) {
                        $join .= "\n" . $joins[$this->type][$join_table];
                        unset($joins[$this->type][$join_table]);
                    }
                }
            }
            // Get rid of the original Exif data (for listings)
            $null_db = $this->type === 'images'
                ? ', NULL as image_original_exifdata '
                : null;
            $query = 'SELECT * '
                . $null_db
                . 'FROM (SELECT * FROM '
                . $base_table
                . $join
                . $this->where
                . $order_by
                . $limit
                . ') '
                . $base_table;
            if (! empty($joins[$this->type])) {
                $query .= "\n"
                    . implode("\n", $joins[$this->type]);
            }
            $query .= $order_by;
        }
        $db = DB::getInstance();
        $this->query = $query;
        $db->query($this->query);
        foreach ($this->binds as $bind) {
            $db->bind($bind['param'], $bind['value'], $bind['type'] ?? null);
        }
        $this->output = $db->fetchAll() ?: [];
        $this->output_count = $db->rowCount();
        $this->has_page_next = $db->rowCount() > $this->limit;
        $this->has_page_prev = $this->offset > 0;
        if ($this->reverse) {
            $this->output = array_reverse($this->output);
        }
        $start = current($this->output);
        $end = end($this->output);
        $seekEnd = $end[$sort_field] ?? '';
        $seekStart = $start[$sort_field] ?? '';
        if ($this->sort_type === 'id') {
            $seekEnd = encodeID((int) $seekEnd);
            $seekStart = encodeID((int) $seekStart);
        } else {
            if (is_array($end)) {
                $seekEnd .= '.' . encodeID((int) $end[$key_field]);
            }
            if (is_array($start)) {
                $seekStart .= '.' . encodeID((int) $start[$key_field]);
            }
        }
        if (! $this->has_page_next) {
            $seekEnd = '';
        }
        if (! $this->has_page_prev) {
            $seekStart = '';
        }
        $this->seekEnd = $seekEnd;
        $this->seekStart = $seekStart;
        if ($db->rowCount() > $this->limit) {
            array_pop($this->output);
        }
        $this->output = safe_html(var: $this->output, skip: ['album_cta', 'tag_name']);
        $this->count = count($this->output);
        $this->nsfw = false;
        $this->output_assoc = [];
        $formatfn = 'Chevereto\Legacy\Classes\\' . ucfirst(substr($this->type, 0, -1));
        foreach ($this->output as $k => $v) {
            $val = $formatfn::formatArray($v);
            $this->output_assoc[] = $val;
            if (! $this->nsfw && isset($val['nsfw']) && $val['nsfw']) {
                $this->nsfw = true;
            }
        }
        if ($this->type === 'albums') {
            $this->nsfw = false;
        }
        $this->sfw = ! $this->nsfw;
        Handler::setCond('show_viewer_zero', isset(request()['viewer']) && $this->count > 0);
        if ($this->type === 'albums' && $this->output !== []) {
            $coverTpl = '(SELECT *
            FROM %tImages%
            LEFT JOIN %tStorages% ON %tImages%.image_storage_id = %tStorages%.storage_id
            WHERE image_id = (SELECT album_cover_id FROM %tAlbums% WHERE album_id = %ALBUM_ID%)
            AND %tImages%.image_is_approved = 1
            LIMIT 1)';
            $album_cover_qry_tpl = strtr($coverTpl, [
                '%tImages%' => $tables['images'],
                '%tStorages%' => $tables['storages'],
                '%tAlbums%' => $tables['albums'],
            ]);
            $albums_cover_qry_arr = [];
            $albums_mapping = [];
            foreach ($this->output as $k => &$album) {
                $album['album_id'] ??= '';
                $album['album_image_count'] ??= 0;
                if ($album['album_image_count'] < 0) {
                    $album['album_image_count'] = 0;
                }
                $album['album_image_count_label'] = _n('image', 'images', $album['album_image_count']);
                $albums_cover_qry_arr[] = str_replace(
                    '%ALBUM_ID%',
                    $album['album_id'],
                    $album_cover_qry_tpl
                );
                $albums_mapping[$album['album_id']] = $k;
            }
            $albums_slice_qry = implode("\n" . 'UNION ALL ' . "\n", $albums_cover_qry_arr);
            $db->query($albums_slice_qry);
            $albums_slice = $db->fetchAll();
            if (! empty($albums_slice)) {
                foreach ($albums_slice as $slice) {
                    $album_key = $albums_mapping[$slice['image_album_id']] ?? null;
                    if ($album_key === null) {
                        continue;
                    }
                    if (! isset($this->output[$album_key]['album_images_slice'])) {
                        $this->output[$album_key]['album_images_slice'] = [];
                    }
                    $this->output[$album_key]['album_images_slice'][] = $slice;
                }
            }
        }
    }

    public static function getTabs($args = [], $autoParams = [], $expanded = false)
    {
        $default = [
            'list' => true,
            'REQUEST' => request(),
            'listing' => 'explore',
            'basename' => get_route_name(),
            'tools' => true,
            'tools_available' => [],
        ];
        $args = array_merge($default, $args);
        $semantics = [
            'recent' => [
                'icon' => 'fas fa-history',
                'label' => _s('Recent'),
                'content' => 'all',
                'sort' => 'date_desc',
            ],
            'trending' => [
                'icon' => 'fas fa-chart-simple',
                'label' => _s('Trending'),
                'content' => 'all',
                'sort' => 'views_desc',
            ],
        ];
        // Criteria -> images | albums | users | tags
        // Criteria -> [CONTENT TABS]
        $criterias = [
            'top-users' => [
                'icon' => 'fas fa-crown',
                'label' => _s('Top'),
                'sort' => 'image_count_desc',
                'content' => 'users',
            ],
            'most-recent' => [
                'icon' => $semantics['recent']['icon'],
                'label' => _s('Most recent'),
                'sort' => 'date_desc',
                'content' => 'all',
            ],
            'most-oldest' => [
                'icon' => 'fas fa-fast-backward',
                'label' => _s('Oldest'),
                'sort' => 'date_asc',
                'content' => 'all',
            ],
            'most-viewed' => [
                'icon' => $semantics['trending']['icon'],
                'label' => _s('Most viewed'),
                'sort' => 'views_desc',
                'content' => 'all',
            ],
        ];
        if (Settings::get('enable_likes')) {
            $semantics['popular'] = [
                'content' => 'all',
                'content_exclude' => ['tags'],
                'icon' => 'fas fa-heart',
                'label' => _s('Popular'),
                'sort' => 'likes_desc',
            ];
            $criterias['most-liked'] = [
                'content' => 'all',
                'content_exclude' => ['tags'],
                'icon' => 'fas fa-heart',
                'label' => _s('Most liked'),
                'sort' => 'likes_desc',
            ];
        }
        $base_criteria = [
            'icon' => 'fas fa-sort-alpha-down',
            'label' => 'AZ',
        ];
        $criterias['album-az-asc'] = array_merge($base_criteria, [
            'sort' => 'name_asc',
            'content' => 'albums',
        ]);
        $criterias['image-az-asc'] = array_merge($base_criteria, [
            'sort' => 'title_asc',
            'content' => 'images',
        ]);
        $criterias['tags-az-asc'] = array_merge($base_criteria, [
            'sort' => 'name_asc',
            'content' => 'tags',
        ]);
        $criterias['user-az-asc'] = array_merge($base_criteria, [
            'sort' => 'username_asc',
            'content' => 'users',
        ]);
        if (isset($args['order'])) {
            $criterias = array_merge(array_flip($args['order']), $criterias);
        }
        $listings = [
            'explore' => [
                'label' => _s('Explore'),
                'content' => 'images',
            ],
            'animated' => [
                'label' => _s('Animated'),
                'content' => 'images',
                'where' => 'image_is_animated = 1',
                // 'semantic' => true,
            ],
            'search' => [
                'label' => _s('Search'),
                'content' => 'all',
                'content_exclude' => ['tags'],
            ],
            'users' => [
                'icon' => 'fas fa-users',
                'label' => _s('People'),
                'content' => 'users',
            ],
            'images' => [
                'icon' => 'fas fa-photo-film',
                'label' => _n('File', 'Files', 20),
                'content' => 'images',
            ],
            'videos' => [
                'icon' => 'fas fa-video',
                'label' => _n('Video', 'Videos', 20),
                'content' => 'images',
            ],
            'albums' => [
                'icon' => 'fas fa-images',
                'label' => _n('Album', 'Albums', 20),
                'content' => 'albums',
            ],
            'tags' => [
                'icon' => 'fas fa-tags',
                'label' => _n('Tag', 'Tags', 20),
                'content' => 'tags',
            ],
        ];
        $listings = array_merge($listings, $semantics);
        $parameters = [];
        if (isset($args['listing'], $listings[$args['listing']])) {
            $parameters = $listings[$args['listing']];
        }
        if (isset($args['exclude_criterias']) && is_array($args['exclude_criterias'])) {
            foreach ($args['exclude_criterias'] as $exclude) {
                if (array_key_exists($exclude, $criterias)) {
                    unset($criterias[$exclude]);
                }
            }
        }
        // Content -> most recent | oldest | most viewed | most liked
        // Content -> [CRITERIA TABS]
        $contents = [
            'images' => [
                'icon' => $listings['images']['icon'],
                'label' => $listings['images']['label'],
            ],
            'albums' => [
                'icon' => $listings['albums']['icon'],
                'label' => $listings['albums']['label'],
            ],
            'tags' => [
                'icon' => $listings['tags']['icon'],
                'label' => $listings['tags']['label'],
            ],
        ];
        if ((bool) env()['CHEVERETO_ENABLE_USERS']) {
            $contents['users'] = [
                'icon' => $listings['users']['icon'],
                'label' => _n('User', 'Users', 20),
            ];
        }
        $i = 0;
        $currentKey = null;
        if (! isset($parameters['content'])) {
            $parameters['content'] = '';
        }

        $iterate = $parameters['content'] === 'all'
            ? $contents
            : (isset($parameters['semantic'])
                ? $semantics
                : $criterias);
        $tabs = [];
        foreach ($iterate as $k => $v) {
            if ($parameters['content'] === 'tags'
                && in_array($parameters['content'], $v['content_exclude'] ?? [])
            ) {
                continue;
            }
            if (in_array($k, $parameters['content_exclude'] ?? [])) {
                continue;
            }
            if ($parameters['content'] === 'all') {
                $content = $k;
                $id = 'list-' . $args['listing'] . '-' . $content; // list-popular-images
                $sort = $parameters['sort'] ?? 'date_desc';
            } else {
                $content = $parameters['content'];
                if ($v['content'] !== 'all' && $v['content'] !== $content) {
                    continue;
                }
                $id = 'list-' . $k; // list-most-oldest
                $sort = $v['sort'];
            }
            if (! $content) {
                $content = 'images'; // explore
            }
            $basename = $args['basename'];
            $default_params = [
                'list' => $content,
                'sort' => $sort,
                'page' => '1',
            ];
            $params = $args['params'] ?? $default_params;
            if (isset($args['params_remove_keys'])) {
                foreach ((array) $args['params_remove_keys'] as $key) {
                    unset($params[$key]);
                }
            }
            if (isset($args['params'])
                && is_array($args['params'])
                && array_key_exists('q', $args['params'])
                && $args['listing'] === 'search'
            ) {
                $args['params_hidden']['list'] = $content;
                $basename .= '/' . $content;
            }
            if (isset($args['params_hidden'])) {
                foreach (array_keys((array) $args['params_hidden']) as $kk) {
                    if (array_key_exists($kk, $params)) {
                        unset($params[$kk]);
                    }
                }
            }
            if (($args['tag'] ?? '') !== '') {
                $params['tag'] = $args['tag'];
            }
            $http_build_query = http_build_query($params);
            $url = get_base_url($basename . '/?' . $http_build_query);
            $current = isset($args['REQUEST'], $args['REQUEST']['sort'])
                ? $args['REQUEST']['sort'] == ($v['sort'] ?? false)
                : false;
            if ($i === 0 && ! $current) {
                $current = ! isset($args['REQUEST']['sort']);
            }
            if ($current && $currentKey === null) {
                $currentKey = $i;
            }
            $tab = [
                'icon' => $v['icon'] ?? null,
                'list' => (bool) $args['list'],
                'tools' => in_array($content, ['users', 'tags'])
                    ? false :
                    (bool) $args['tools'],
                'tools_available' => $args['tools_available'] ?? [],
                'label' => $v['label'],
                'id' => $id,
                'params' => $http_build_query,
                'current' => false,
                'type' => $content,
                'url' => $url,
            ];
            if ($args['tools_available'] !== []
                && ! Handler::cond('allowed_to_delete_content')
                && array_key_exists('delete', $args['tools_available'])
            ) {
                unset($args['tools_available']['delete']);
            }
            if ($args['tools_available'] === []) {
                unset($tab['tools_available']);
            }
            if (isset($args['params_hidden'])) {
                $tab['params_hidden'] = http_build_query($args['params_hidden']);
            }
            $tabs[] = $tab;
            unset($id, $params, $basename, $http_build_query, $content, $current);
            $i++;
        }
        if ($currentKey === null) {
            $currentKey = 0;
            if ($parameters['content'] === 'all') {
                foreach ($tabs as $k => &$v) {
                    if (isset($args['REQUEST']['list']) && $v['type'] === $args['REQUEST']['list']) {
                        $currentKey = $k;

                        break;
                    }
                }
            }
        }
        $tabs[$currentKey]['current'] = 1;
        self::fillCurrentTabPeekSeek($tabs, $currentKey, $autoParams);
        if ($expanded) {
            return [
                'tabs' => $tabs,
                'currentKey' => $currentKey,
            ];
        }

        return $tabs;
    }

    public static function fillCurrentTabPeekSeek(array &$tabs, $currentKey, array $autoParams): void
    {
        foreach (['peek', 'seek'] as $pick) {
            $picked = $autoParams[$pick] ?? null;
            if (isset($picked)) {
                $pickedString = "&{$pick}=" . rawurlencode($picked);
                $tabs[$currentKey]['params'] .= $pickedString;
                $tabs[$currentKey]['url'] .= $pickedString;

                break;
            }
        }
    }

    public function htmlOutput($tpl_list = null)
    {
        if ($tpl_list === 'files') {
            $tpl_list = 'images';
        }
        if (! is_array($this->output)) {
            return;
        }
        if ($tpl_list === null) {
            $tpl_list = $this->type ?: 'images';
        }
        $directory = new RecursiveDirectoryIterator(PATH_PUBLIC_LEGACY_THEME . 'tpl_list_item/');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        $list_item_template = [];
        foreach ($regex as $file) {
            $file = forward_slash($file[0]);
            $key = preg_replace('/\\.[^.\\s]{3,4}$/', '', str_replace(PATH_PUBLIC_LEGACY_THEME, '', $file));
            $override_file = str_replace_first(PATH_PUBLIC_LEGACY_THEME, PATH_PUBLIC_LEGACY_THEME . 'overrides/', $file);
            if (is_readable($override_file)) {
                $file = $override_file;
            }
            ob_start();
            require $file;
            $file_get_contents = ob_get_contents();
            ob_end_clean();
            $list_item_template[$key] = $file_get_contents;
        }
        $html_output = '';
        $tpl_list = preg_replace('/s$/', '', $tpl_list);
        if (function_exists('get_peafowl_item_list')) {
            $render = 'get_peafowl_item_list';
        } else {
            $render = 'Chevereto\Legacy\get_peafowl_item_list';
        }
        $tools = $this->tools ?: [];
        $doTags = in_array($tpl_list, ['image', 'user/image', 'album/image'])
            && $this->tools
            && (
                $this->owner === ($this->requester['id'] ?? false)
                || ($this->requester['is_content_manager'] ?? false)
            );
        if ($doTags) {
            $tags = [];
            $fileToTags = [];
            $ids = array_column($this->output, 'image_id');
            $ids = array_map(fn ($id) => (int) $id, $ids);
            $tagsFilesTable = DB::getTable('tags_files');
            $filesTable = DB::getTable('images');
            $tagsTable = DB::getTable('tags');
            $inFiles = implode(',', $ids);
            $tagsSQL = <<<MySQL
            SELECT tf.tag_file_tag_id id, tags.tag_name name, tf.tag_file_file_id file_id
            FROM `{$tagsFilesTable}` tf
            LEFT JOIN `{$filesTable}` files ON tf.tag_file_file_id = files.image_id
            LEFT JOIN `{$tagsTable}` tags ON tf.tag_file_tag_id = tags.tag_id
            WHERE tf.tag_file_file_id IN ({$inFiles});

            MySQL;
            $fetchTags = DB::queryFetchAll($tagsSQL);
            foreach ($fetchTags as $tag) {
                $tagId = $tag['id'];
                $fileId = $tag['file_id'];
                if (! isset($fileToTags[$fileId])) {
                    $fileToTags[$fileId] = [];
                }
                if (! isset($tags[$tagId])) {
                    $tag = Tag::row($tag['name']);
                    $tags[$tagId] = $tag;
                } else {
                    $tag = $tags[$tagId];
                }
                $fileToTags[$fileId][] = $tag;
            }
        }
        $tagFn = require_theme_file_return('snippets/tag');
        $items = [];
        foreach ($this->output as $pos => &$row) {
            switch ($tpl_list) {
                case 'image':
                case 'user/image':
                case 'album/image':
                case 'user/liked/image':
                default:
                    $Class = Image::class;
                    $imageId = $row['image_id'];
                    $row['image_tags'] = [];
                    if ($fileToTags[$imageId] ?? false) {
                        $row['image_tags'] = $fileToTags[$imageId];
                        $row['image_tags_string'] = implode(', ', array_column($row['image_tags'], 'name'));
                    }

                    break;
                case 'album':
                case 'user/album':
                case 'user/liked/album':
                    $Class = Album::class;

                    break;
                case 'tag':
                    $Class = Tag::class;

                    break;
                case 'user':
                case 'user/user':
                    $Class = User::class;

                    break;
            }
            $item = $Class::formatArray($row);
            if (str_ends_with($tpl_list, 'album') && $this->tagsString !== '') {
                $item['url'] .= '/?tag=' . rawurlencode($this->tagsString);
            }
            $items[] = $item;
            if ($tpl_list === 'tag') {
                $html_output .= $tagFn(
                    color: 'default',
                    url: $item['url'],
                    name: $item['name_safe_html'],
                );

                continue;
            }
            $html_output .= $render(
                item: $item,
                template: $list_item_template,
                tools: $tools,
                tpl: $tpl_list,
                requester: $this->requester,
                pos: $pos,
            );
        }

        return $html_output;
    }

    public static function getAlbumHtml($album_id, $template = 'user/albums')
    {
        $listing = new self();
        $listing->setType('albums');
        $listing->setOffset(0);
        $listing->setLimit(1);
        $listing->setSortType('date');
        $listing->setSortOrder('desc');
        $listing->setWhere('WHERE album_id=:album_id');
        $listing->bind(':album_id', $album_id);
        $listing->exec();

        return $listing->htmlOutput($template);
    }

    public static function getParams($request = [], bool $json_call = false, string $type = '')
    {
        self::setValidSortTypes();
        $items_per_page = getSetting('listing_items_per_page');
        $listingSafeCount = Settings::LISTING_SAFE_COUNT;
        $listing_pagination_mode = getSetting('listing_pagination_mode');
        if ($type === 'tags') {
            // $items_per_page = 200;
            $listingSafeCount = 200;
        }
        $params = [];
        $params['offset'] = 0;
        $params['items_per_page'] = $items_per_page;
        if (! $json_call && $listing_pagination_mode === 'endless') {
            $params['page'] = max((int) ($request['page'] ?? 0), 1);
            $params['limit'] = $params['items_per_page'] * $params['page'];
            if ($params['limit'] > $listingSafeCount) {
                $listing_pagination_mode = 'classic';
                Settings::setValue('listing_pagination_mode', $listing_pagination_mode);
            }
        }
        if (isset($request['pagination']) || $listing_pagination_mode === 'classic') { // Static single page display
            $params['page'] = empty($request['page']) ? 0 : (int) ($request['page'] ?? 0) - 1;
            $params['limit'] = $params['items_per_page'];
            $params['offset'] = $params['page'] * $params['limit'];
        }
        if ($json_call) {
            $params = array_merge($params, [
                'page' => empty($request['page'])
                    ? 0
                    : ((int) $request['page']) - 1,
                'limit' => $items_per_page,
            ]);
            $params['offset'] = $params['page'] * $params['limit']
                + ($request['offset'] ?? 0);
        }
        $default_sort = [
            0 => 'date',
            1 => 'desc',
        ];
        preg_match('/(.*)_(asc|desc)/', $request['sort'] ?? '', $sort_matches);
        $params['sort'] = array_slice($sort_matches, 1);
        if (count($params['sort']) !== 2) {
            $params['sort'] = $default_sort;
        }
        if (! in_array($params['sort'][0], self::$valid_sort_types, true)) {
            $params['sort'][0] = $default_sort[0];
        }
        if (! in_array($params['sort'][1], ['asc', 'desc'], true)) {
            $params['sort'][1] = $default_sort[1];
        }
        if (! empty($request['seek'])) {
            $params['seek'] = $request['seek'];
        } elseif (! empty($request['peek'])) {
            $params['seek'] = $request['peek'];
            $params['reverse'] = true;
        }
        $params['page_show'] = empty($request['page']) ? null : (int) $request['page'];

        return $params;
    }

    /**
     * validate_input aka "first stage validation"
     * This checks for valid input source data before exec
     * @Exception 1XX
     */
    protected function validateInput()
    {
        self::setValidSortTypes();
        if (empty($this->offset)) {
            $this->offset = 0;
        }
        $check_missing = ['type', 'offset', 'limit', 'sort_type', 'sort_order'];
        missing_values_to_exception($this, Exception::class, $check_missing, 600);
        if (! in_array($this->type, self::$valid_types, true)) {
            throw new Exception('Invalid $type "' . $this->type . '"', 610);
        }
        if ($this->offset < 0 || $this->limit < 0) {
            throw new Exception('Limit integrity violation', 621);
        }
        if (! in_array($this->sort_type, self::$valid_sort_types, true)) {
            throw new Exception('Invalid $sort_type "' . $this->sort_type . '"', 630);
        }
        if (! preg_match('/^(asc|desc)$/', $this->sort_order)) {
            throw new Exception('Invalid $sort_order "' . $this->sort_order . '"', 640);
        }
    }

    protected static function setValidSortTypes()
    {
        if (getSetting('enable_likes') && ! in_array('likes', self::$valid_sort_types, true)) {
            self::$valid_sort_types[] = 'likes';
        }
    }

    private function getWhere(string $where): string
    {
        return ($this->where === '' ? 'WHERE ' : ($this->where . ' AND ')) . $where;
    }
}
