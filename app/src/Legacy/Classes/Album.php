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

use function Chevereto\Encryption\decrypt;
use function Chevereto\Encryption\encrypt;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\assertNotStopWords;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\check_value;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\datetimegmt_convert_tz;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\seoUrlfy;
use function Chevereto\Legacy\G\truncate;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\send_mail;
use function Chevereto\Legacy\time_elapsed_string;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;
use function Emoji\detect_emoji;
use Exception;
use Throwable;

class Album
{
    public const ENCRYPTED_NAMES = [
        'password'
    ];

    public static function getSingle(
        int $id,
        bool $sumview = false,
        bool $pretty = true,
        array $requester = []
    ): array {
        $tables = DB::getTables();
        $query = 'SELECT * FROM ' . $tables['albums'] . "\n";
        $joins = [
            'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['albums'] . '.album_user_id = ' . $tables['users'] . '.user_id'
        ];
        if ($requester !== []) {
            if (version_compare(Settings::get('chevereto_version_installed'), '3.9.0', '>=')) {
                $joins[] = 'LEFT JOIN ' . $tables['likes'] . ' ON ' . $tables['likes'] . '.like_content_type = "album" AND ' . $tables['albums'] . '.album_id = ' . $tables['likes'] . '.like_content_id AND ' . $tables['likes'] . '.like_user_id = ' . $requester['id'];
            }
        }
        $query .= implode("\n", $joins) . "\n";
        $query .= 'WHERE album_id=:album_id;' . "\n";
        if ($sumview) {
            $query .= 'UPDATE ' . $tables['albums'] . ' SET album_views = album_views + 1 WHERE album_id=:album_id';
        }
        $db = DB::getInstance();
        $db->query($query);
        $db->bind(':album_id', $id);
        $album_db = $db->fetchSingle();
        if (!isset($album_db)
            || !is_array($album_db)
            || !$album_db) {
            return [];
        }
        if ($sumview) {
            $album_db['album_views'] ??= 0;
            $album_db['album_views'] += 1;
            Stat::track([
                'action' => 'update',
                'table' => 'albums',
                'value' => '+1',
                'user_id' => $album_db['album_user_id'],
            ]);
        }
        if ($requester !== []) {
            $album_db['album_liked'] = (bool) $album_db['like_user_id'];
        }

        return $pretty
            ? self::formatArray($album_db)
            : self::cipherAwareDbRow($album_db);
    }

    public static function getMultiple(array $ids, bool $pretty = false): array
    {
        if ($ids === []) {
            throw new Exception('Empty ids provided', 600);
        }
        $tables = DB::getTables();
        $query = 'SELECT * FROM ' . $tables['albums'] . "\n";
        $joins = [
            'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['albums'] . '.album_user_id = ' . $tables['users'] . '.user_id'
        ];
        $query .= implode("\n", $joins) . "\n";
        $query .= 'WHERE album_id IN (' . implode(',', $ids) . ')' . "\n";
        $db = DB::getInstance();
        $db->query($query);
        $db_rows = $db->fetchAll();
        if (hasEncryption()) {
            foreach ($db_rows as &$row) {
                if (isset($row['album_password'])) {
                    try {
                        $row['album_password'] = decrypt($row['album_password']);
                    } catch (Throwable) {
                        $row['album_password'] = $row['album_password'];
                    }
                }
            }
        }
        if ($pretty) {
            $return = [];
            foreach ($db_rows as $k => $v) {
                $return[$k] = self::formatArray($v);
            }

            return $return;
        }

        return $db_rows;
    }

    public static function sumView(int $id, array $album = []): void
    {
        if (($album['id'] ?? 0) !== $id) {
            $album = self::getSingle($id);
            if ($album === []) {
                throw new Exception(sprintf('Invalid album id %s', $id), 600);
            }
        }
        $increment = '+1';
        DB::increment('albums', ['views' => $increment], ['id' => $id]);
        Stat::track([
            'action' => 'update',
            'table' => 'albums',
            'value' => $increment,
            'user_id' => $album['album_user_id'],
        ]);
        $addValue = session()['album_view_stock'];
        $addValue[] = $id;
        sessionVar()->put('album_view_stock', $id);
    }

    public static function getUrl(string $id_encoded, string $title = null): string
    {
        $seo = seoUrlfy($title ?? '');
        $url = $seo == ''
            ? $id_encoded
            : ($seo . '.' . $id_encoded);

        return get_base_url(
            (getSetting('root_route') === 'album'
                ? ''
                : getSetting('route_album') . '/')
            . $url
        );
    }

    public static function insert(array $values): int
    {
        Stat::assertMax('albums');
        if (!isset($values['user_id'])) {
            $values['user_id'] = null;
        }
        if (!isset($values['description'])) {
            $values['description'] = '';
        }
        if (($values['privacy'] ?? null) == 'password') {
            if (!check_value($values['password'])) {
                throw new Exception('Missing album password', 100);
            }
            if (hasEncryption()) {
                $values = encryptValues(self::ENCRYPTED_NAMES, $values);
            }
        }
        $flood = self::handleFlood();
        if ($flood !== []) {
            throw new Exception(
                _s(
                    'Flooding detected. You can only upload %limit% %content% per %time%',
                    [
                        '%content%' => _n('album', 'albums', $flood['limit']),
                        '%limit%' => $flood['limit'],
                        '%time%' => $flood['by']
                    ]
                ),
                130
            );
        }
        if (!isset($values['name'])) {
            $values['name'] = _s('Unnamed') . ' ' . datetime();
        }
        $privacyOpts = ['public', 'password', 'private_but_link'];
        if (Login::isLoggedUser()) {
            $privacyOpts[] = 'private';
        }
        if (in_array($values['privacy'], $privacyOpts) == false) {
            $values['privacy'] = 'public';
        }
        nullify_string($values['description']);
        if (empty($values['creation_ip'])) {
            $values['creation_ip'] = get_client_ip();
        }
        assertNotStopWords($values['name'] ?? '', $values['description'] ?? ''); // @phpstan-ignore-line
        $album_array = [
            'name' => $values['name'],
            'user_id' => $values['user_id'],
            'date' => datetime(),
            'date_gmt' => datetimegmt(),
            'privacy' => $values['privacy'],
            'password' => $values['privacy'] == 'password' ? $values['password'] : null,
            'description' => $values['description'],
            'creation_ip' => $values['creation_ip'],
            'parent_id' => $values['parent_id'] ?? null
        ];
        $insert = DB::insert('albums', $album_array);
        if (Login::isLoggedUser()) {
            DB::increment('users', ['album_count' => '+1'], ['id' => $values['user_id']]);
        } else {
            $addValue = session()['guest_albums'] ?? [];
            $addValue[] = $insert;
            sessionVar()->put('guest_albums', $addValue);
        }
        Stat::track([
            'action' => 'insert',
            'table' => 'albums',
            'value' => '+1',
            'date_gmt' => $album_array['date_gmt']
        ]);

        return $insert;
    }

    public static function moveContents(int|string|array $from, ?int $to = null): bool
    {
        $ids = is_array($from) ? $from : [$from];
        $db = DB::getInstance();
        $db->query('UPDATE ' . DB::getTable('albums') . ' SET album_parent_id=:album_parent_id WHERE album_id IN (' . implode(',', $ids) . ')');
        $db->bind(':album_parent_id', $to);

        return $db->exec();
    }

    public static function addImage(int $album_id, int $id)
    {
        return self::addImages($album_id, [$id]);
    }

    public static function addImages(?int $album_id, array $ids)
    {
        if ($ids === []) {
            throw new Exception('Empty ids provided', 600);
        }
        $images = Image::getMultiple($ids, true);
        $albums = [];
        foreach ($images as $k => $v) {
            if (isset($v['album']['id']) && $v['album']['id'] != $album_id) {
                $album_k = $v['album']['id'];
                if (!array_key_exists($album_k, $albums)) {
                    $albums[$album_k] = [];
                }
                $albums[$album_k][] = $v['id'];
            }
        }
        $db = DB::getInstance();
        $db->query('UPDATE `' . DB::getTable('images') . '` SET `image_album_id`=:image_album_id WHERE `image_id` IN (' . implode(',', $ids) . ')');
        $db->bind(':image_album_id', $album_id);
        $exec = $db->exec();
        if ($exec && $db->rowCount() > 0) {
            if (!is_null($album_id)) {
                self::updateImageCount($album_id, $db->rowCount());
            }
            if ($albums !== []) {
                $album_query = '';
                $album_query_tpl = 'UPDATE `' . DB::getTable('albums') . '` SET `album_image_count` = GREATEST(`album_image_count` - :counter, 0) WHERE `album_id` = :album_id;';
                foreach ($albums as $k => $v) {
                    $album_query .= strtr($album_query_tpl, [':counter' => count($v), ':album_id' => $k]);
                }
                $db = DB::getInstance();
                $db->query($album_query);
                $db->exec();
            }
        }
        $db = DB::getInstance();
        $db->query('UPDATE `' . DB::getTable('albums') . '` SET `album_cover_id` = NULL WHERE `album_cover_id` IN(' . implode(',', $ids) . ');');
        $db->exec();
        $album = Album::getSingle((int) $album_id);
        if (!isset($album['cover_id']) && is_int($album_id)) {
            self::populateCover($album_id);
        }

        return $exec;
    }

    public static function update(int $id, array $values)
    {
        if (array_key_exists('description', $values)) {
            nullify_string($values['description']);
        }
        assertNotStopWords($values['name'] ?? '', $values['description'] ?? '');
        if (isset($values['password']) && hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_NAMES, $values);
        }

        return DB::update('albums', $values, ['id' => $id]);
    }

    public static function populateCover(int $id)
    {
        $db = DB::getInstance();
        $db->query('UPDATE `' . DB::getTable('albums') . '`
                SET album_cover_id = (SELECT image_id FROM `' . DB::getTable('images') . '` WHERE image_album_id = album_id AND image_is_approved = 1 ORDER BY image_id DESC LIMIT 1)
                WHERE album_id = :album_id;');
        $db->bind(':album_id', $id);
        $db->exec();
    }

    public static function delete(int $id)
    {
        $images_deleted = 0;
        $user_id = DB::get('albums', ['id' => $id])[0]['album_user_id'] ?? null;
        $album = self::getSingle($id);
        if ($album === []) {
            return false;
        }
        foreach (DB::get('albums', ['parent_id' => $id]) as $child) {
            $images_deleted += static::delete((int) $child['album_id']);
        }
        $delete = DB::delete('albums', ['id' => $id]);
        if ($delete === 0) {
            return false;
        }
        $db = DB::getInstance();
        $db->query('SELECT image_id FROM ' . DB::getTable('images') . ' WHERE image_album_id=:image_album_id');
        $db->bind(':image_album_id', $id);
        $album_image_ids = $db->fetchAll();
        foreach ($album_image_ids as $k => $v) {
            if (Image::delete((int) $v['image_id'], false) !== 0) { // We will update the user counts (image + album) at once
                $images_deleted++;
            }
        }
        if (isset($user_id)) {
            $user_updated_counts = [
                'album_count' => '-1',
                'image_count' => '-' . $images_deleted
            ];
            DB::increment('users', $user_updated_counts, ['id' => $user_id]);
        }
        DB::delete('notifications', ['content_type' => 'album', 'type_id' => $id]);
        Stat::track([
            'action' => 'delete',
            'table' => 'albums',
            'value' => '-1',
            'date_gmt' => $album['date_gmt']
        ]);

        return $images_deleted;
    }

    public static function deleteMultiple(array $ids)
    {
        $affected = 0;
        foreach ($ids as $id) {
            $affected += self::delete((int) $id);
        }

        return $affected;
    }

    public static function updateImageCount(int $id, int $counter = 1, string $operator = '+')
    {
        $query = 'UPDATE `' . DB::getTable('albums') . '` SET `album_image_count` = ';
        if (in_array($operator, ['+', '-'])) {
            $query .= 'GREATEST(`album_image_count` ' . $operator . ' ' . $counter . ', 0)';
        } else {
            $query .= $counter;
        }
        $query .= ' WHERE `album_id` = :album_id';
        $db = DB::getInstance();
        $db->query($query);
        $db->bind(':album_id', $id);

        return $db->exec();
    }

    public static function fill(array &$album, array &$user = [])
    {
        $album['id_encoded'] = isset($album['id'])
            ? encodeID((int) $album['id'])
            : null;
        if (!isset($album['name']) && isset($user['id'])) {
            $album['name'] = User::getStreamName($user['username']);
        }
        if (!isset($album['id'])) {
            $album['url'] = $user !== [] ? User::getUrl($user['username']) : null;
            $album['url_short'] = $album['url'];
        } else {
            $album['url'] = self::getUrl($album['id_encoded'], getSetting('seo_album_urls') ? $album['name'] : '');
            $album['url_short'] = self::getUrl($album['id_encoded'], '');
        }
        $album['name_html'] = safe_html($album['name'] ?? '');
        if (!isset($album['privacy'])) {
            $album['privacy'] = "public";
        }
        switch ($album['privacy']) {
            case 'private_but_link':
                $album['privacy_notes'] = _s('Note: This content is private but anyone with the link will be able to see this.');

                break;
            case 'password':
                $album['privacy_notes'] = _s('Note: This content is password protected. Remember to pass the content password to share.');

                break;
            case 'private':
                $album['privacy_notes'] = _s('Note: This content is private. Change privacy to "public" to share.');

                break;
            default:
                $album['privacy_notes'] = null;

                break;
        }
        $private_str = _s('Private');
        $privacy_to_label = [
            'public' => _s('Public'),
            'private' => $private_str . '/' . _s('Me'),
            'private_but_link' => $private_str . '/' . _s('Link'),
            'password' => $private_str . '/' . _s('Password'),
        ];
        $album['privacy_readable'] = $privacy_to_label[$album['privacy']];
        $album['name_with_privacy_readable'] = ($album['name'] ?? '') . ' (' . $album['privacy_readable'] . ')';
        $album['name_with_privacy_readable_html'] = safe_html($album['name_with_privacy_readable']);
        $album['name_truncated'] = truncate($album['name'] ?? '', 28);
        $album['name_truncated_html'] = safe_html($album['name_truncated']);
        if (!empty($user)) {
            User::fill($user);
        }
        $display_url = '';
        $display_width = '';
        $display_height = '';
        if (!empty($album['cover_id'])) {
            $image = Image::getSingle((int) $album['cover_id']);
            if ($image !== []) {
                $image = DB::formatRow($image);
                unset($image['album']);
                Image::fill($image);
                $display_url = $image['display_url'];
                $display_width = $image['display_width'];
                $display_height = $image['display_height'];
            }
            $album['cover_id_encoded'] = encodeID((int) $album['cover_id']);
        }
        if (!empty($album['parent_id'])) {
            $album['parent_id_encoded'] = encodeID((int) $album['parent_id']);
        }
        $album['display_url'] = $display_url;
        $album['display_width'] = $display_width;
        $album['display_height'] = $display_height;
        if (!isset($album['date_gmt'])) {
            $album['date_gmt'] = $user['date_gmt'] ?? datetimegmt();
        }
        $album['date_fixed_peer'] = Login::isLoggedUser()
            ? datetimegmt_convert_tz($album['date_gmt'], Login::getUser()['timezone'])
            : $album['date_gmt'];
        $ctaArray = [];
        if ($album['cta_enable'] ?? false) {
            try {
                $ctaArray = json_decode($album['cta'] ?? '', true) ?? [];
                foreach ($ctaArray as &$v) {
                    $icon = $v['icon'];
                    $iconClass = '';
                    $emoji = detect_emoji($v['icon']);
                    if ($emoji === []) {
                        $icon = '';
                        if (preg_match('/\s/', $v['icon']) === 1) {
                            $iconClass = $v['icon'];
                        } else {
                            $iconClass = 'fas fa-' . $v['icon'];
                        }
                    }
                    $v['iconClass'] = $iconClass;
                    $v['emoji'] = $icon;
                }
            } catch (Throwable) {
                $ctaArray = [];
            }
        }
        $album['cta_array'] = $ctaArray;
        $album['cta_array_json'] = json_encode($album['cta_array']);
        $album['cta_html'] = '';
        foreach ($album['cta_array'] as $button) {
            $album['cta_html'] .= <<<STRING
            <a class="btn btn-cta btn-small animate" title="{$button['label']}" href="{$button['href']}"><span class="btn-icon {$button['iconClass']}">{$button['emoji']}</span><span class="btn-text">{$button['label']}</span></a>
            STRING;
        }
        $album['cta'] = $album['cta'] ?? '[]';
    }

    public static function cipherAwareDbRow(array &$dbrow): array
    {
        if (isset($dbrow['album_password']) && hasEncryption()) {
            try {
                $dbrow['album_password'] = decrypt($dbrow['album_password']);
            } catch (Throwable) {
                $dbrow['album_password'] = $dbrow['album_password'];
            }
        }

        return $dbrow;
    }

    public static function formatArray(array $dbrow, bool $safe = false): array
    {
        self::cipherAwareDbRow($dbrow);
        $output = DB::formatRow($dbrow);
        if (!isset($output['user'])) {
            $output['user'] = [];
        }
        self::fill($output, $output['user']);
        $output['views_label'] = _n('view', 'views', $output['views'] ?? 0);
        $output['how_long_ago'] = time_elapsed_string($output['date_gmt'] ?? '');
        if (isset($output['images_slice'])) {
            foreach ($output['images_slice'] as &$v) {
                $v = Image::formatArray($v, $safe);
                $v['flag'] = $v['nsfw'] ? 'unsafe' : 'safe';
            }
        }
        if ($safe) {
            unset(
                $output['id'], $output['privacy_extra'], $output['cover_id'], $output['parent_id'],
                $output['user']['id'],
            );
        }

        return $output;
    }

    public static function storeUserPassword($album_id, $user_password): void
    {
        $addValue = session()['password'];
        if (hasEncryption()) {
            $user_password = encrypt($user_password);
        }
        $addValue['album'][$album_id] = $user_password;
        sessionVar()->put('password', $addValue);
    }

    public static function checkSessionPassword($album = []): bool
    {
        $session_password = session()['password']['album'][$album['id']] ?? null;
        if (isset($session_password) && hasEncryption()) {
            $session_password = decrypt($session_password);
        }
        if (!isset($session_password) || !hash_equals($album['password'], $session_password)) {
            $removeValue = session()['password'] ?? null;
            unset($removeValue['album'][$album['id']]);
            sessionVar()->put('password', $removeValue);

            return false;
        }

        return true;
    }

    protected static function handleFlood(): array
    {
        if (!getSetting('flood_uploads_protection') || Login::isAdmin()) {
            return [];
        }
        $flood_limit = [
            'minute' => 20,
            'hour' => 200,
            'day' => 400,
            'week' => 2000,
            'month' => 10000
        ];

        try {
            $db = DB::getInstance();
            $flood_db = $db->queryFetchSingle(
                "SELECT
			COUNT(IF(album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MINUTE), 1, NULL)) AS minute,
			COUNT(IF(album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR), 1, NULL)) AS hour,
			COUNT(IF(album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY), 1, NULL)) AS day,
			COUNT(IF(album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 WEEK), 1, NULL)) AS week,
			COUNT(IF(album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH), 1, NULL)) AS month
			FROM " . DB::getTable('albums') . " WHERE album_creation_ip='" . get_client_ip() . "' AND album_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH)"
            );
        } catch (Exception) {
            $flood_db = false;
        } // Silence
        if ($flood_db === false) {
            return [];
        }
        $is_flood = false;
        $flood_by = '';
        foreach (['minute', 'hour', 'day', 'week', 'month'] as $v) {
            if ($flood_db[$v] >= $flood_limit[$v]) {
                $flood_by = $v;
                $is_flood = true;

                break;
            }
        }
        if ($is_flood) {
            if (!isset(session()['flood_albums_notify'], session()['flood_albums_notify'][$flood_by])) {
                try {
                    $logged_user = Login::getUser();
                    $message_report = '<html><body>' . "\n";
                    $message_report .= strtr('Flooding IP <a href="' . get_public_url('search/images/?q=ip:%ip') . '">%ip</a>', ['%ip' => get_client_ip()]) . '<br>';
                    $message_report .= 'User <a href="' . $logged_user['url'] . '">' . $logged_user['name'] . '</a><br>';
                    $message_report .= '<br>';
                    $message_report .= '<b>Albums per time period</b><br>';
                    $message_report .= 'Minute: ' . $flood_db['minute'] . "<br>";
                    $message_report .= 'Hour: ' . $flood_db['hour'] . "<br>";
                    $message_report .= 'Week: ' . $flood_db['day'] . "<br>";
                    $message_report .= 'Month: ' . $flood_db['week'] . "<br>";
                    $message_report .= '</body></html>';
                    send_mail(getSetting('email_incoming_email'), 'Flood report user ID ' . $logged_user['id'], $message_report);
                    $addValue = session()['flood_albums_notify'];
                    $addValue[$flood_by] = true;
                    sessionVar()->put('flood_albums_notify', $addValue);
                } catch (Exception) {
                } // Silence
            }

            return ['flood' => true, 'limit' => $flood_limit[$flood_by], 'count' => $flood_db[$flood_by], 'by' => $flood_by];
        }

        return [];
    }
}
