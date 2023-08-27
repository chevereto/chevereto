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
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\ends_with;
use function Chevereto\Legacy\G\forward_slash;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_route_name;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\missing_values_to_exception;
use function Chevereto\Vars\env;
use function Chevereto\Vars\request;
use DateTime;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class Listing
{
    public string $query;

    private int $offset;

    public array $seek;

    private array $params_hidden;

    private int $limit;

    private string $sort_type;

    private string $sort_order;

    private int $owner;

    private array $requester = [];

    private $privacy;

    private int $output_count = 0;

    private bool $has_page_next;

    public string $seekEnd = '';

    public string $seekStart = '';

    public int $count = 0;

    public bool $nsfw;

    private array $output_assoc = [];

    private bool $sfw = true;

    private bool $has_page_prev;

    private int $isApproved = 1;

    public static array $valid_types = ['images', 'albums', 'users'];

    public static array $valid_sort_types = ['date_gmt', 'size', 'views', 'id', 'image_count', 'name', 'title', 'username'];

    public array $output = [];

    private array $binds = [];

    private string $type;

    private int $category;

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
        if (!isset($this->query)) {
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
        $this->type = $type;
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
                1 => decodeID($explode[$last])
            ];
            $this->seek = $array;

            return;
        }
        $decodeID = decodeID($seek);
        if (ctype_digit(strval($decodeID))) {
            $this->seek = ['0000-01-01 00:00:00', $decodeID];
        }
    }

    public function setReverse($bool)
    {
        $this->reverse = $bool;
    }

    public function setParamsHidden($params)
    {
        $this->params_hidden = $params;
    }

    // Sets the limit (sql> LIMIT offset,limit)
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    // Sets the sort type (sql> SORT BY sort_type)
    public function setSortType($sort_type)
    {
        $this->sort_type = $sort_type == 'date' ? 'date_gmt' : $sort_type;
    }

    // Sets the sort order (sql> DESC | ASC)
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
    }

    // Sets the WHERE clause
    public function setWhere(string $where)
    {
        $this->where = $where;
    }

    public function setOwner(int $user_id)
    {
        $this->owner = $user_id;
    }

    public function setRequester(array $user)
    {
        $this->requester = $user;
    }

    public function setCategory($category)
    {
        $this->category = (int) $category;
    }

    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    public function setTools(array|bool $flag)
    {
        $this->tools = $flag;
    }

    public function bind($param, $value, $type = null)
    {
        $this->binds[] = [
            'param' => $param,
            'value' => $value,
            'type' => $type
        ];
    }

    private function getWhere(string $where): string
    {
        return ($this->where == '' ? 'WHERE ' : ($this->where . ' AND ')) . $where;
    }

    /**
     * Do the thing
     * @Exeption 4xx
     */
    public function exec()
    {
        $this->validateInput();
        $tables = DB::getTables();
        if ($this->requester === []) {
            $this->setRequester(Login::getUser());
        }
        if ($this->type == 'images') {
            $this->where = $this->getWhere('image_is_approved = ' . (int) $this->isApproved);
        }
        if (!(bool) env()['CHEVERETO_ENABLE_USERS']) {
            $userId = getSetting('website_mode_personal_uid') ?? 0;
            $this->where = match ($this->type) {
                'images' => $this->getWhere('image_user_id=' . $userId),
                'albums' => $this->getWhere('album_user_id=' . $userId),
                default => $this->where
            };
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
                'users' => 'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['albums'] . '.album_user_id = ' . $tables['users'] . '.user_id'
            ]
        ];
        if ($this->type == 'users' && $this->sort_type == 'views') {
            $this->sort_type = 'content_views';
        }
        if (isset($this->params_hidden)) {
            $emptyTypeClauses['users'][] = 'user_image_count > 0 OR user_avatar_filename IS NOT NULL OR user_background_filename IS NOT NULL';
            if ($this->sort_type == 'views') {
                $emptyTypeClauses['albums'][] = 'album_views > 0';
                $emptyTypeClauses['images'][] = 'image_views > 0';
                $emptyTypeClauses['users'][] = 'user_content_views > 0';
            }
            if ($this->sort_type == 'likes') {
                $emptyTypeClauses['albums'][] = 'album_likes > 0';
                $emptyTypeClauses['images'][] = 'image_likes > 0';
                $emptyTypeClauses['users'][] = 'user_likes > 0';
            }
            if ($this->type == 'albums') {
                if (isset($this->params_hidden['album_min_image_count']) && $this->params_hidden['album_min_image_count'] > 0) {
                    $whereClauses[] = sprintf('album_image_count >= %d', $this->params_hidden['album_min_image_count']);
                } else {
                    $emptyTypeClauses['albums'][] = 'album_image_count > 0';
                }
            }
            if (array_key_exists($this->type, $emptyTypeClauses) && isset($this->params_hidden['hide_empty']) && $this->params_hidden['hide_empty'] == 1) {
                $whereClauses[] = '(' . implode(') AND (', $emptyTypeClauses[$this->type]) . ')';
            }
            if (isset($this->params_hidden['hide_banned']) && $this->params_hidden['hide_banned'] == 1) {
                $whereClauses[] = '(' . $tables['users'] . '.user_status IS NULL OR ' . $tables['users'] . '.user_status <> "banned"' . ')';
            }
            if ($this->type == 'images' && isset($this->params_hidden['is_animated']) && $this->params_hidden['is_animated'] == 1) {
                $whereClauses[] = 'image_is_animated = 1';
            }
            if (!empty($whereClauses)) {
                $whereClauses = implode(' AND ', $whereClauses);
                $this->where = $this->getWhere($whereClauses);
            }
        }
        $type_singular = DB::getFieldPrefix($this->type);
        if ($this->where !== '') {
            $where_clauses = explode(' ', str_ireplace('WHERE ', '', $this->where));
            $where_arr = [];
            foreach ($where_clauses as $clause) {
                if (!preg_match('/\./', $clause)) {
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
        if (version_compare(Settings::get('chevereto_version_installed'), '3.7.0', '>=')) {
            // Dynamic since v3.9.0
            $likes_join = 'LEFT JOIN ' . $tables['likes'] . ' ON ' . $tables['likes'] . '.like_content_type = "' . $type_singular . '" AND ' . $tables['likes'] . '.like_content_id = ' . $tables[$this->type] . '.' . $type_singular . '_id';
            if (preg_match('/like_user_id/', $this->where)) {
                $joins[$this->type]['likes'] = $likes_join;
            } elseif ($this->requester !== [] && $this->type !== 'users') {
                $joins[$this->type]['likes'] = $likes_join . ' AND ' . $tables['likes'] . '.like_user_id = ' . $this->requester['id'];
            }
            $follow_tpl_join = 'LEFT JOIN ' . $tables['follows'] . ' ON ' . $tables['follows'] . '.%FIELD = ' . $tables[$this->type] . '.' . ($this->type == 'users' ? 'user' : DB::getFieldPrefix($this->type) . '_user') . '_id';
            if (preg_match('/follow_user_id/', $this->where)) {
                $joins[$this->type]['follows'] = strtr($follow_tpl_join, ['%FIELD' => 'follow_followed_user_id']);
            }
            if (preg_match('/follow_followed_user_id/', $this->where)) {
                $joins[$this->type]['follows'] = strtr($follow_tpl_join, ['%FIELD' => 'follow_user_id']);
            }
        }
        // Add ID reservation clause
        if ($this->type == 'images') {
            $res_id_where = 'image_size > 0';
            if ($this->where == '') {
                $this->where = 'WHERE ' . $res_id_where;
            } else {
                $this->where .= ' AND ' . $res_id_where;
            }
        }
        // Add category clause
        if ($this->type == 'images' && isset($this->category)) {
            $category_qry = $tables['images'] . '.image_category_id = ' . $this->category;
            if ($this->where == '') {
                $this->where = 'WHERE ' . $category_qry;
            } else {
                $this->where .= ' AND ' . $category_qry;
            }
        }
        // Privacy layer
        if (
            !($this->requester['is_admin'] ?? false)
            && in_array($this->type, ['images', 'albums', 'users'])
            && (
                (!isset($this->owner) || $this->requester === []) || $this->owner !== $this->requester['id']
            )
        ) {
            if ($this->where == '') {
                $this->where = 'WHERE ';
            } else {
                $this->where .= ' AND ';
            }
            $nsfw_off = $this->requester !== []
                ? !$this->requester['show_nsfw_listings']
                : !getSetting('show_nsfw_in_listings');
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
                if (getSetting('website_privacy_mode') == 'public' || $this->privacy == 'private_but_link' || getSetting('website_content_privacy_mode') == 'default') {
                    $this->where .= '(' . $tables['albums'] . '.album_privacy NOT IN';
                    $privacy_modes = ['private', 'private_but_link', 'custom'];
                    if ($this->type === 'images') {
                        $privacy_modes[] = 'password';
                    }
                    if (isset($this->privacy) && in_array($this->privacy, $privacy_modes)) {
                        unset($privacy_modes[array_search($this->privacy, $privacy_modes)]);
                    }
                    $this->where .= " (" . "'" . implode("','", $privacy_modes) . "'" . ") ";
                    $this->where .= "OR " . $tables['albums'] . '.album_privacy IS NULL';
                    if ($this->requester !== []) {
                        $this->where .= ' OR ' . $tables['albums'] . '.album_user_id =' . $this->requester['id'];
                    }
                    $this->where .= ')';
                } else {
                    $injected_requester = $this->requester['id'] ?? '0';
                    $this->where .= '(' . $tables['albums'] . '.album_user_id = ' . $injected_requester;
                    $this->where .= $this->type == 'albums' ? ')' : (' OR ' . $tables['images'] . '.image_user_id = ' . $injected_requester . ')');
                }
            }
        }
        $sort_field = $type_singular . '_' . $this->sort_type;
        $key_field = $type_singular . '_id';
        if (isset($this->seek)) {
            if (ends_with('date_gmt', $this->sort_type)) {
                $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->seek[0]);
                if (!$d || $d->format('Y-m-d H:i:s') !== $this->seek[0]) {
                    $this->seek = ['0000-01-01 00:00:00', $this->seek[1]];
                }
            }
            if ($this->where == '') {
                $this->where = 'WHERE ';
            } else {
                $this->where .= ' AND ';
            }
            if ($this->reverse) {
                $this->sort_order = $this->sort_order == 'asc' ? 'desc' : 'asc';
            }
            $signo = $this->sort_order == 'desc' ? '<=' : '>=';
            if ($this->sort_type == 'id') {
                $this->where .= $sort_field . ' ' . $signo . ' :seek';
                $this->bind(':seek', $this->seek);
            } else {
                $signo = $this->sort_order == 'desc' ? '<' : '>';
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
        if (in_array($this->sort_type, ['name', 'title', 'username'])) {
            $order_by .= 'CAST(' . $table_order . '.' . $sort_field . ' as CHAR) ' . $sort_order . ', ';
            $order_by .= 'LENGTH(' . $table_order . '.' . $sort_field . ') ' . $sort_order . ', ';
        }
        $order_by .= '' . $table_order . '.' . $sort_field . ' ' . $sort_order;
        if ($this->sort_type != 'id') {
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
                    if (!empty($joins[$this->type][$join_table])) {
                        $join .= "\n" . $joins[$this->type][$join_table];
                        unset($joins[$this->type][$join_table]);
                    }
                }
            }
            // Get rid of the original Exif data (for listings)
            $null_db = $this->type == 'images' ? ', NULL as image_original_exifdata ' : null;
            $query = 'SELECT * ' . $null_db . 'FROM (SELECT * FROM ' . $base_table . $join . $this->where . $order_by . $limit . ') ' . $base_table;
            if (!empty($joins[$this->type])) {
                $query .= "\n" . implode("\n", $joins[$this->type]);
            }
            $query .= $order_by;
        }
        $db = DB::getInstance();
        $this->query = $query;
        $db->query($this->query);
        foreach ($this->binds as $bind) {
            $db->bind($bind['param'], $bind['value'], $bind['type'] ?? null);
        }
        $this->output = $db->fetchAll();
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
        if ($this->sort_type == 'id') {
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
        if (!$this->has_page_next) {
            $seekEnd = '';
        }
        if (!$this->has_page_prev) {
            $seekStart = '';
        }
        $this->seekEnd = $seekEnd;
        $this->seekStart = $seekStart;
        if ($db->rowCount() > $this->limit) {
            array_pop($this->output);
        }
        $this->output = safe_html(var: $this->output, skip: ['album_cta']);
        $this->count = count($this->output);
        $this->nsfw = false;
        $this->output_assoc = [];
        $formatfn = 'Chevereto\Legacy\Classes\\' . ucfirst(substr($this->type, 0, -1));
        foreach ($this->output as $k => $v) {
            $val = $formatfn::formatArray($v);
            $this->output_assoc[] = $val;
            if (!$this->nsfw && isset($val['nsfw']) && $val['nsfw']) {
                $this->nsfw = true;
            }
        }
        if ($this->type === 'albums') {
            $this->nsfw = false;
        }
        $this->sfw = !$this->nsfw;
        Handler::setCond('show_viewer_zero', isset(request()['viewer']) && $this->count > 0);
        if ($this->type == 'albums' && $this->output !== []) {
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
                // @phpstan-ignore-next-line
                $album['album_image_count'] ??= 0;
                // @phpstan-ignore-next-line
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
            if (!empty($albums_slice)) {
                foreach ($albums_slice as $slice) {
                    $album_key = $albums_mapping[$slice['image_album_id']] ?? null;
                    if ($album_key === null) {
                        continue;
                    }
                    if (!isset($this->output[$album_key]['album_images_slice'])) {
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
                'icon' => 'fas fa-poll',
                'label' => _s('Trending'),
                'content' => 'all',
                'sort' => 'views_desc',
            ],
        ];
        // Criteria -> images | albums | users
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
                'icon' => 'fas fa-heart',
                'label' => _s('Popular'),
                'content' => 'all',
                'sort' => 'likes_desc',
            ];
            $criterias['most-liked'] = [
                'icon' => 'fas fa-heart',
                'label' => _s('Most liked'),
                'sort' => 'likes_desc',
                'content' => 'all',
            ];
        }
        $criterias['album-az-asc'] = [
            'icon' => 'fas fa-sort-alpha-down',
            'label' => 'AZ',
            'sort' => 'name_asc',
            'content' => 'albums',
        ];
        $criterias['image-az-asc'] = [
            'icon' => 'fas fa-sort-alpha-down',
            'label' => 'AZ',
            'sort' => 'title_asc',
            'content' => 'images',
        ];
        $criterias['user-az-asc'] = [
            'icon' => 'fas fa-sort-alpha-down',
            'label' => 'AZ',
            'sort' => 'username_asc',
            'content' => 'users',
        ];
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
                'semantic' => true,
            ],
            'search' => [
                'label' => _s('Search'),
                'content' => 'all',
            ],
            'users' => [
                'icon' => 'fas fa-users',
                'label' => _s('People'),
                'content' => 'users',
            ],
            'images' => [
                'icon' => 'fas fa-image',
                'label' => _s('Images'),
                'content' => 'images',
            ],
            'albums' => [
                'icon' => 'fas fa-images',
                'label' => _n('Album', 'Albums', 20),
                'content' => 'albums',
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
                'label' => _s('Images'),
            ],
            'albums' => [
                'icon' => $listings['albums']['icon'],
                'label' => _n('Album', 'Albums', 20),
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
        if (!isset($parameters['content'])) {
            $parameters['content'] = '';
        }
        $iterate = ($parameters['content'] == 'all' ? $contents : (isset($parameters['semantic']) ? $semantics : $criterias));
        $tabs = [];
        foreach ($iterate as $k => $v) {
            if ($parameters['content'] == 'all') {
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
            if (!$content) {
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
            if (isset($args['params']) && is_array($args['params']) && array_key_exists('q', $args['params']) && $args['listing'] == 'search') {
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
            $http_build_query = http_build_query($params);
            $url = get_base_url($basename . '/?' . $http_build_query);
            $current = isset($args['REQUEST'], $args['REQUEST']['sort']) ? $args['REQUEST']['sort'] == ($v['sort'] ?? false) : false;
            if ($i == 0 && !$current) {
                $current = !isset($args['REQUEST']['sort']);
            }
            if ($current && is_null($currentKey)) {
                $currentKey = $i;
            }
            $tab = [
                'icon' => $v['icon'] ?? null,
                'list' => (bool) $args['list'],
                'tools' => $content == 'users' ? false : (bool) $args['tools'],
                'tools_available' => $args['tools_available'],
                'label' => $v['label'],
                'id' => $id,
                'params' => $http_build_query,
                'current' => false,
                'type' => $content,
                'url' => $url
            ];
            if ($args['tools_available'] && !Handler::cond('allowed_to_delete_content') && array_key_exists('delete', $args['tools_available'])) {
                unset($args['tools_available']['delete']);
            }
            if ($args['tools_available'] == null) {
                unset($tab['tools_available']);
            }
            if (isset($args['params_hidden'])) {
                $tab['params_hidden'] = http_build_query($args['params_hidden']);
            }
            $tabs[] = $tab;
            unset($id, $params, $basename, $http_build_query, $content, $current);
            $i++;
        }
        if (is_null($currentKey)) {
            $currentKey = 0;
            if ($parameters['content'] == 'all') {
                foreach ($tabs as $k => &$v) {
                    if (isset($args['REQUEST']['list']) && $v['type'] == $args['REQUEST']['list']) {
                        $currentKey = $k;

                        break;
                    }
                }
            }
        }
        $tabs[$currentKey]['current'] = 1;
        self::fillCurrentTabPeekSeek($tabs, $currentKey, $autoParams);
        if ($expanded) {
            return ['tabs' => $tabs, 'currentKey' => $currentKey];
        }

        return $tabs;
    }

    public static function fillCurrentTabPeekSeek(array &$tabs, $currentKey, array $autoParams): void
    {
        foreach (['peek', 'seek'] as $pick) {
            $picked = $autoParams[$pick] ?? null;
            if (isset($picked)) {
                $pickedString = "&$pick=" . urlencode($picked);
                $tabs[$currentKey]['params'] .= $pickedString;
                $tabs[$currentKey]['url'] .= $pickedString;

                break;
            }
        }
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
        if (!in_array($this->type, self::$valid_types)) {
            throw new Exception('Invalid $type "' . $this->type . '"', 610);
        }
        if ($this->offset < 0 || $this->limit < 0) {
            throw new Exception('Limit integrity violation', 621);
        }
        if (!in_array($this->sort_type, self::$valid_sort_types)) {
            throw new Exception('Invalid $sort_type "' . $this->sort_type . '"', 630);
        }
        if (!preg_match('/^(asc|desc)$/', $this->sort_order)) {
            throw new Exception('Invalid $sort_order "' . $this->sort_order . '"', 640);
        }
    }

    protected static function setValidSortTypes()
    {
        if (getSetting('enable_likes') && !in_array('likes', self::$valid_sort_types)) {
            self::$valid_sort_types[] = 'likes';
        }
    }

    public function htmlOutput($tpl_list = null)
    {
        if (!is_array($this->output)) {
            return;
        }
        if (is_null($tpl_list)) {
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
        $requester = Login::getUser();
        foreach ($this->output as $row) {
            switch ($tpl_list) {
                case 'image':
                case 'user/image':
                case 'album/image':
                default: // key thing here...
                    $Class = Image::class;

                    break;
                case 'album':
                case 'user/album':
                    $Class = Album::class;

                    break;
                case 'user':
                case 'user/user':
                    $Class = User::class;

                    break;
            }
            $item = $Class::formatArray($row);
            $html_output .= $render($item, $list_item_template, $tools, $tpl_list, $requester);
        }

        return $html_output;
    }

    public static function getAlbumHtml($album_id, $template = 'user/albums')
    {
        $listing = new Listing();
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

    public static function getParams($request = [], bool $json_call = false)
    {
        self::setValidSortTypes();
        $items_per_page = getSetting('listing_items_per_page');
        $listing_pagination_mode = getSetting('listing_pagination_mode');
        $params = [];
        $params['offset'] = 0;
        $params['items_per_page'] = $items_per_page;
        if (!$json_call && $listing_pagination_mode == 'endless') {
            $params['page'] = max((int) ($request['page'] ?? 0), 1);
            $params['limit'] = $params['items_per_page'] * $params['page'];
            if ($params['limit'] > getSetting('listing_safe_count')) {
                $listing_pagination_mode = 'classic';
                Settings::setValue('listing_pagination_mode', $listing_pagination_mode);
            }
        }
        if (isset($request['pagination']) || $listing_pagination_mode == 'classic') { // Static single page display
            $params['page'] = empty($request['page']) ? 0 : (int) ($request['page'] ?? 0) - 1;
            $params['limit'] = $params['items_per_page'];
            $params['offset'] = $params['page'] * $params['limit'];
        }
        if ($json_call) {
            $params = array_merge($params, [
                'page' => empty($request['page']) ? 0 : $request['page'] - 1,
                'limit' => $items_per_page
            ]);
            $params['offset'] = $params['page'] * $params['limit'] + ($request['offset'] ?? 0);
        }
        $default_sort = [
            0 => 'date',
            1 => 'desc'
        ];
        preg_match('/(.*)_(asc|desc)/', $request['sort'] ?? '', $sort_matches);
        $params['sort'] = array_slice($sort_matches, 1);
        if (count($params['sort']) !== 2) {
            $params['sort'] = $default_sort;
        }
        if (!in_array($params['sort'][0], self::$valid_sort_types)) {
            $params['sort'][0] = $default_sort[0];
        }
        if (!in_array($params['sort'][1], ['asc', 'desc'])) {
            $params['sort'][1] = $default_sort[1];
        }
        if (!empty($request['seek'])) {
            $params['seek'] = $request['seek'];
        } elseif (!empty($request['peek'])) {
            $params['seek'] = $request['peek'];
            $params['reverse'] = true;
        }
        $params['page_show'] = empty($request['page']) ? null : (int) $request['page'];

        return $params;
    }
}
