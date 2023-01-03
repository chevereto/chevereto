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

use function Chevereto\Legacy\assertNotStopWords;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\abbreviate_number;
use function Chevereto\Legacy\G\absolute_to_relative;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\is_route_available;
use function Chevereto\Legacy\G\is_url_web;
use function Chevereto\Legacy\G\linkify;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\rrmdir;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\get_redirect_url;
use function Chevereto\Legacy\get_users_image_url;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\linkify_redirector;
use function Chevereto\Legacy\system_notification_email;
use function Chevereto\Vars\env;
use Exception;

class User
{
    public static function getSingle(mixed $var, string $by = 'id', bool $pretty = true): array
    {
        $user_db = DB::get('users', [$by => $var], 'AND', [], 1);
        if (!is_array($user_db)
            || $user_db === []
        ) {
            return [];
        }
        $connections = Login::getUserConnections($user_db['user_id']);
        $aux = [];
        foreach ($connections as $connection) {
            $aux[$connection['name']] = $connection;
        }
        $user_db['user_login'] = $aux;
        $user_db['user_connections_count'] = count($connections);
        foreach (['user_image_count', 'user_album_count'] as $v) {
            if (is_null($user_db[$v]) || $user_db[$v] < 0) {
                $user_db[$v] = 0;
            }
        }
        $user_db['user_is_admin'] ??= false;
        $user_db['user_is_manager'] ??= false;
        $user_db['user_is_content_manager'] = $user_db['user_is_admin'] || $user_db['user_is_manager'];
        if (!array_key_exists('user_following', $user_db)) {
            $user_db['user_following'] = 0;
        }
        if (!array_key_exists('user_followers', $user_db)) {
            $user_db['user_followers'] = 0;
        }
        if (isset($user_db['user_name'])) {
            $user_db['user_name'] = self::sanitizeUserName($user_db['user_name']);
        }
        if ($pretty) {
            $user_db = self::formatArray($user_db);
        }

        return $user_db;
    }

    public static function getPrivate(): array
    {
        return [
            'id' => 0,
            'name' => _s('Private profile'),
            'username' => 'private',
            'name_short' => _s('Private'),
            'url' => get_public_url(),
            'album_count' => 0,
            'image_count' => 0,
            'image_count_label' => _n('image', 'images', 0),
            'album_count_display' => 0,
            'image_count_display' => 0,
            'is_private' => true
        ];
    }

    public static function getAlbums(int|array $var): array
    {
        $id = is_array($var) ? $var['id'] : $var;
        $user_albums = [];
        $user_stream = self::getStreamAlbum($var);
        if (is_array($user_stream)) {
            $user_albums['stream'] = $user_stream;
        }
        $map = [];
        $children = [];
        $db = DB::getInstance();
        $db->query('SELECT * FROM ' . DB::getTable('albums') . ' WHERE album_user_id=:image_user_id ORDER BY album_parent_id ASC, album_name ASC LIMIT :limit');
        $db->bind(':limit', intval(env()['CHEVERETO_MAX_USER_ALBUMS_LIST'] ?? 300));
        $db->bind(':image_user_id', $id);
        $user_albums_db = $db->fetchAll();
        if ($user_albums_db) {
            $user_albums += $user_albums_db;
        }
        foreach ($user_albums as $k => &$v) {
            $album_id = isset($v['album_id'])
                ? $v['album_id']
                : 'stream';
            $map[$album_id] = $k;
            $parent_id = $v['album_parent_id'] ?? null;
            if (isset($v['album_image_count']) && $v['album_image_count'] < 0) {
                $v['album_image_count'] = 0;
            }
            $children[$parent_id][$album_id] = $v['album_name'];
            if (isset($parent_id)) {
                asort($children[$parent_id]);
            }
        }
        if (count($children[''] ?? []) == 0) {
            return [];
        }
        $list = [];
        foreach (array_keys($children['']) as $key) {
            self::iterate((string) $key, $children, $list, $user_albums, $map, 0);
        }

        return $list;
    }

    private static function iterate(
        string $key,
        array $array,
        array &$list,
        array $albums,
        array $map,
        int $level
    ): void {
        $album = $albums[$map[$key]];
        $album['album_indent'] = $level;
        $album['album_indent_string'] = '';
        if ($level > 0) {
            $album['album_indent_string'] = str_repeat('─', $level) . ' ';
        }
        $album = DB::formatRow($album, 'album');
        Album::fill($album);
        if ($key == 'stream') {
            $list[$key] = $album;
        } else {
            $list[] = $album;
        }
        if (!isset($array[$key])) {
            return;
        }
        $level++;
        foreach (array_keys($array[$key]) as $k) {
            self::iterate((string) $k, $array, $list, $albums, $map, $level);
        }
    }

    public static function getStreamAlbum(int|array $user): ?array
    {
        if (!is_array($user)) {
            $user = self::getSingle($user, 'id', true);
        }
        if ($user !== []) {
            return [
                'album_id' => null,
                'album_id_encoded' => null,
                'album_name' => self::getStreamName($user['username']),
                'album_user_id' => $user['id'],
                'album_privacy' => 'public',
                'album_url' => $user['url']
            ];
        }

        return null;
    }

    public static function getStreamName(string $username): string
    {
        return _s("%s by %u", ['%s' => _s('Images'), '%u' => $username]);
    }

    public static function getUrl(array|string $handle)
    {
        $username = is_array($handle) ? ($handle[isset($handle['user_username']) ? 'user_username' : 'username'] ?? null) : $handle;
        $id = is_array($handle) ? ($handle[isset($handle['user_id']) ? 'user_id' : 'id'] ?? null) : null;
        $path = getSetting('root_route') === 'user'
            ? ''
            : getSetting('route_user') . '/';
        $url = $path . $username;
        if (is_array($handle) && getSetting('website_mode') == 'personal' && $id == getSetting('website_mode_personal_uid')) {
            $url = getSetting('website_mode_personal_routing') !== '/' ? getSetting('website_mode_personal_routing') : '';
        }

        return get_base_url($url);
    }

    public static function getUrlAlbums(string $user_url): string
    {
        return rtrim($user_url, '/') . '/albums';
    }

    public static function insert(array $values): int
    {
        Stat::assertMax('users');
        if (!isset($values['date'])) {
            $values['date'] = datetime();
        }
        if (!isset($values['date_gmt'])) {
            $values['date_gmt'] = datetimegmt();
        }
        if (!isset($values['language'])) {
            $values['language'] = getSetting('default_language');
        }
        if (!isset($values['timezone'])) {
            $values['timezone'] = getSetting('default_timezone');
        }
        if (isset($values['name'])) {
            $values['name'] = self::sanitizeUserName($values['name']);
        }
        if (!isset($values['registration_ip'])) {
            $values['registration_ip'] = get_client_ip();
        }
        if (!isset($values['palette_id'])) {
            $values['palette_id'] = intval(getSetting('theme_palette'));
        }
        assertNotStopWords($values['name'] ?? '', $values['bio'] ?? '');
        if (!Login::isAdmin()) {
            $db = DB::getInstance();
            $db->query('SELECT COUNT(*) c FROM ' . DB::getTable('users') . ' WHERE user_registration_ip=:ip AND user_status != "valid" AND user_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 2 DAY)');
            $db->bind(':ip', $values['registration_ip']);
            if ($db->fetchSingle()['c'] > 5) {
                throw new Exception('Flood detected', 666);
            }
        }
        $user_id = DB::insert('users', $values);
        if (!Login::isAdmin() && Settings::get('notify_user_signups')) {
            $message = implode('<br>', [
                'A new user has just signed up %user (%edit)',
                '',
                'Username: %username',
                'Email: %email',
                'Status: %status',
                'IP: %registration_ip',
                'Date (GMT): %date_gmt',
                '',
                'You can disable these notifications on %configure'
            ]);
            foreach (['username', 'email', 'status', 'registration_ip', 'date_gmt'] as $k) {
                $table['%' . $k] = $values[$k] ?? '';
            }
            $table['%edit'] = '<a href="' . get_public_url('dashboard/user/' . $user_id) . '">edit</a>';
            $table['%user'] = '<a href="' . get_public_url(self::getUrl($values['username'])) . '">' . $values['username'] . '</a>';
            $table['%configure'] = '<a href="' . get_public_url('dashboard/settings/users') . '">dashboard/settings/users</a>';
            system_notification_email([
                'subject' => sprintf('New user signup %s', $values['username']),
                'message' => strtr($message, $table),
            ]);
        }
        Stat::track([
            'action' => 'insert',
            'table' => 'users',
            'value' => '+1',
            'date_gmt' => $values['date_gmt'],
            'user_id' => $user_id,
        ]);

        return $user_id;
    }

    public static function update(int|string $id, array $values): int
    {
        if (isset($values['name'])) {
            $values['name'] = self::sanitizeUserName($values['name']);
        }
        assertNotStopWords($values['name'] ?? '', $values['bio'] ?? '');

        return DB::update('users', $values, ['id' => (int) $id]);
    }

    public static function uploadPicture(int|array $user, string $type, array|string $source): ?array
    {
        $type = strtolower($type);
        if (!in_array($type, ['background', 'avatar'])) {
            throw new Exception('Invalid upload type', 600);
        }
        if (!is_array($user)) {
            $user = self::getSingle($user, 'id');
        }
        if ($user === []) {
            throw new Exception("target user doesn't exists", 601);
        }
        $localPath = PATH_PUBLIC_CONTENT_IMAGES_USERS . $user['id_encoded'] . '/';
        $storagePath = ltrim(absolute_to_relative($localPath), '/');
        $image_upload = Image::upload(
            $source,
            $localPath,
            ($type == 'avatar' ? 'av' : 'bkg') . '_' . strtotime(datetimegmt()),
            ['max_size' => get_bytes(Settings::get('user_image_' . $type . '_max_filesize_mb') . ' MB')]
        );
        /** @var array $uploaded */
        $uploaded = $image_upload['uploaded'];
        if ($type == 'avatar') {
            $max_res = ['width' => 500, 'height' => 500, 'fitted' => true];
            $must_resize = $uploaded['fileinfo']['width'] > $max_res['width']
                || $uploaded['fileinfo']['height'] > $max_res['height'];
        } else {
            $max_res = ['width' => 1920];
            $must_resize = $uploaded['fileinfo']['width'] > $max_res['width'];
            $medium = Image::resize(
                $uploaded['file'],
                null,
                $uploaded['name'] . '.md',
                [
                    'width' => 500,
                    'over_resize' => true,
                ]
            );
            $toStorage[] = [
                'file' => $medium['file'],
                'filename' => $medium['filename'],
                'mime' => $medium['fileinfo']['mime'],
            ];
        }
        if ($must_resize) {
            $uploaded = Image::resize($uploaded['file'], null, null, $max_res);
        }
        $toStorage[] = [
            'file' => $uploaded['file'],
            'filename' => $uploaded['filename'],
            'mime' => $uploaded['fileinfo']['mime'],
        ];
        $toDelete = [];
        $convert = new ImageConvert($uploaded['file'], 'jpg', $uploaded['file'], 90);
        $uploaded['file'] = $convert->out();
        $user_edit = self::update($user['id'], [$type . '_filename' => $uploaded['filename']]);
        $assetStorage = AssetStorage::getStorage();
        if ($user_edit !== 0) {
            AssetStorage::uploadFiles($toStorage, ['keyprefix' => $storagePath]);
            if (isset($user[$type])) {
                $image_path = $storagePath . $user[$type]['filename'];
                if ($type == 'background') {
                    $pathinfo = pathinfo($image_path);
                    $image_md_path = str_replace($pathinfo['basename'], $pathinfo['filename'] . '.md.' . $pathinfo['extension'], $image_path);
                    $toDelete[] = ['key' => $image_md_path];
                }
                $toDelete[] = ['key' => $image_path];
            }
            if ($toDelete !== []) {
                AssetStorage::deleteFiles($toDelete);
            }
        }
        if (!AssetStorage::isLocalLegacy()) {
            $toUnlink = [$uploaded['file']];
            if ($type == 'background') {
                $pathinfo = pathinfo($uploaded['file']);
                $image_md_path = str_replace($pathinfo['basename'], $pathinfo['filename'] . '.md.' . $pathinfo['extension'], $uploaded['file']);
                $toUnlink[] = $image_md_path;
            }
            foreach ($toDelete as $delete) {
                $toUnlink[] = PATH_PUBLIC . $delete['key'];
            }
            foreach ($toUnlink as $remove) {
                unlinkIfExists($remove);
            }
        }
        $uploaded['fileinfo']['url'] = str_replace_first(
            URL_APP_PUBLIC,
            $assetStorage['url'],
            $uploaded['fileinfo']['url']
        );

        return $uploaded['fileinfo'];
    }

    public static function deletePicture(int|array $user, string $deleting): bool
    {
        $deleting = strtolower($deleting);
        if (!in_array($deleting, ['background', 'avatar'])) {
            throw new Exception('Invalid delete type', 600);
        }
        if (!is_array($user)) {
            $user = self::getSingle($user, 'id', true);
        }
        if ($user === []) {
            throw new Exception("Target user doesn't exists", 601);
        }
        if (!$user[$deleting]) {
            throw new Exception('user ' . $deleting . " doesn't exists", 602);
        }
        $localPath = PATH_PUBLIC_CONTENT_IMAGES_USERS . $user['id_encoded'] . '/';
        $storagePath = ltrim(absolute_to_relative($localPath), '/');
        $toDelete = [];
        $image_path = $storagePath . $user[$deleting]['filename'];
        if ($deleting == 'background') {
            $pathinfo = pathinfo($image_path);
            $image_md_path = str_replace($pathinfo['basename'], $pathinfo['filename'] . '.md.' . $pathinfo['extension'], $image_path);
            $toDelete[] = ['key' => $image_md_path];
        }
        $toDelete[] = ['key' => $image_path];
        AssetStorage::deleteFiles($toDelete);
        self::update($user['id'], [$deleting . '_filename' => null]);

        return true;
    }

    public static function delete(int|array $user): void
    {
        if (!is_array($user)) {
            $user = self::getSingle($user, 'id', true);
        }
        if ($user === []) {
            return;
        }
        $user_images_path = PATH_PUBLIC_CONTENT_IMAGES_USERS . $user['id_encoded'];
        rrmdir($user_images_path);
        $db = DB::getInstance();
        $db->query('SELECT image_id FROM ' . DB::getTable('images') . ' WHERE image_user_id=:image_user_id');
        $db->bind(':image_user_id', $user['id']);
        $user_images = $db->fetchAll();
        foreach ($user_images as $user_image) {
            Image::delete((int) $user_image['image_id']);
        }
        Notification::delete([
            'table' => 'users',
            'user_id' => $user['id'],
        ]);
        Stat::track([
            'action' => 'delete',
            'table' => 'users',
            'value' => '-1',
            'user_id' => $user['id'],
            'date_gmt' => $user['date_gmt']
        ]);
        $sql = strtr('UPDATE `%table_users` SET user_likes = user_likes - COALESCE((SELECT COUNT(*) FROM `%table_likes` WHERE like_user_id = %user_id AND user_id = like_content_user_id AND like_user_id <> like_content_user_id GROUP BY like_content_user_id),"0");', [
            '%table_users' => DB::getTable('users'),
            '%table_likes' => DB::getTable('likes'),
            '%user_id' => $user['id'],
        ]);
        DB::queryExecute($sql);
        $sql = strtr('UPDATE `%table_users` SET user_followers = user_followers - COALESCE((SELECT 1 FROM `%table_follows` WHERE follow_user_id = %user_id AND user_id = follow_followed_user_id AND follow_user_id <> follow_followed_user_id GROUP BY follow_followed_user_id),"0");', [
            '%table_users' => DB::getTable('users'),
            '%table_follows' => DB::getTable('follows'),
            '%user_id' => $user['id'],
        ]);
        DB::queryExecute($sql);
        $sql = strtr('UPDATE `%table_users` SET user_following = user_following - COALESCE((SELECT 1 FROM `%table_follows` WHERE follow_followed_user_id = %user_id AND user_id = follow_user_id AND follow_user_id <> follow_followed_user_id GROUP BY follow_user_id),"0");', [
            '%table_users' => DB::getTable('users'),
            '%table_follows' => DB::getTable('follows'),
            '%user_id' => $user['id'],
        ]);
        DB::queryExecute($sql);
        DB::delete('albums', ['user_id' => $user['id']]);
        DB::delete('images', ['user_id' => $user['id']]);
        DB::delete('login_connections', ['user_id' => $user['id']]);
        DB::delete('login_cookies', ['user_id' => $user['id']]);
        DB::delete('login_passwords', ['user_id' => $user['id']]);
        DB::delete('likes', ['user_id' => $user['id']]);
        DB::delete('follows', ['user_id' => $user['id'], 'followed_user_id' => $user['id']], 'OR');
        DB::delete('users', ['id' => $user['id']]);
    }

    public static function statusRedirect(?string $status): void
    {
        if ($status === null) {
            return;
        }
        if ($status !== 'valid') {
            if ($status == 'awaiting-email') {
                $status = 'email-needed';
            }
            redirect('account/' . $status);
        }
    }

    public static function isValidUsername(string $string): bool
    {
        $restricted = [
            'tag', 'tags',
            'categories',
            'profile',
            'messages',
            'map',
            'feed',
            'events',
            'notifications',
            'discover',
            'upload',
            'following', 'followers',
            'flow', 'trending', 'popular', 'fresh', 'upcoming', 'editors', 'profiles',
            'activity', 'upgrade', 'account',
            'affiliates', 'billing',
            'do', 'go', 'redirect',
            'api', 'sdk', 'plugin', 'plugins', 'tools',
            'external',
            'importer', 'import', 'exporter', 'export',
        ];
        $virtual_routes = ['image', 'album'];
        foreach ($virtual_routes as $k) {
            $restricted[] = getSetting('route_' . $k);
        }

        return preg_match('/' . getSetting('username_pattern') . '/', $string) === 1 && !in_array($string, $restricted) && !is_route_available($string) && !file_exists(PATH_PUBLIC . $string);
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

    public static function fill(array &$user): void
    {
        $user['palette_id'] = (int) ($user['palette_id'] ?? 0);
        $user['id_encoded'] = encodeID((int) ($user['id'] ?? 0));
        $user['image_count_display'] = isset($user['image_count']) ? abbreviate_number($user['image_count']) : 0;
        $user['album_count_display'] = isset($user['album_count']) ? abbreviate_number($user['album_count']) : 0;
        $user['url'] = self::getUrl($user);
        $user['public_url'] = get_public_url($user['url']);
        $user['url_albums'] = self::getUrlAlbums($user['url']);
        $user['url_liked'] = $user['url'] . '/liked';
        $user['url_following'] = $user['url'] . '/following';
        $user['url_followers'] = $user['url'] . '/followers';
        if (isset($user['website']) && !is_url_web($user['website'])) {
            unset($user['website']);
        }
        if (isset($user['website'])) {
            $user['website_safe_html'] = safe_html($user['website']);
            $user['website_display'] = $user['is_admin'] ? $user['website_safe_html'] : get_redirect_url($user['website_safe_html']);
        }
        if (isset($user['bio'])) {
            $user['bio_safe_html'] = safe_html($user['bio']);
            $user['bio_linkify'] = $user['is_admin']
                ? linkify($user['bio_safe_html'], ['attr' => ['target' => '_blank']])
                : linkify_redirector($user['bio_safe_html']);
        }
        $user['name'] ??= ucfirst($user['username'] ?? '');
        foreach (['image_count', 'album_count'] as $v) {
            $single = $v == 'image_count' ? 'image' : 'album';
            $plural = $v == 'image_count' ? 'images' : 'albums';
            $user[$v . '_label'] = _n($single, $plural, $user[$v] ?? 0);
        }
        $name_array = explode(' ', $user['name'] ?? '');
        $user['firstname'] = mb_strlen($name_array[0]) > 20 ? trim(mb_substr($name_array[0], 0, 20, 'UTF-8')) : $name_array[0];
        $user['firstname_html'] = safe_html(strip_tags($user['firstname']));
        $user['name_short'] = mb_strlen($user['name']) > 20 ? $user['firstname'] : $user['name'];
        $user['name_html'] = safe_html(strip_tags($user['name']));
        $user['name_short_html'] = safe_html(strip_tags($user['name_short']));
        if (isset($user['avatar_filename'])) {
            $avatar_file = $user['id_encoded'] . '/' . $user['avatar_filename'];
            $user['avatar'] = [
                'filename' => $user['avatar_filename'],
                'url' => get_users_image_url($avatar_file)
            ];
        }
        unset($user['avatar_filename']);
        if (isset($user['background_filename'])) {
            $background_file = $user['id_encoded'] . '/' . $user['background_filename'];
            $background_path = PATH_PUBLIC_CONTENT_IMAGES_USERS . $background_file;
            $pathinfo = pathinfo($background_path);
            $background_md_file = $user['id_encoded'] . '/' . $pathinfo['filename'] . '.md.' . $pathinfo['extension'];
            $user['background'] = [
                'filename' => $user['background_filename'],
                'url' => get_users_image_url($user['id_encoded'] . '/' . $user['background_filename']),
                'medium' => [
                    'filename' => $pathinfo['basename'],
                    'url' => get_users_image_url($background_md_file)
                ]
            ];
        }
        unset($user['background_filename'], $user['facebook_username']);
        if (isset($user['twitter_username'])) {
            $user['twitter'] = [
                'username' => $user['twitter_username'],
                'url' => 'http://twitter.com/' . $user['twitter_username']
            ];
        }
        unset($user['twitter_username']);
        if (!isset($user['notifications_unread'])) {
            $user['notifications_unread'] = 0;
        }
        $user['notifications_unread_display'] = $user['notifications_unread'] > 10 ? '+10' : $user['notifications_unread'];
    }

    public static function sanitizeUserName(string $name): string
    {
        return preg_replace('#<|>#', '', $name);
    }

    public static function cleanUnconfirmed(int $limit = null): void
    {
        $db = DB::getInstance();
        $query = 'SELECT * FROM ' . DB::getTable('users') . ' WHERE user_status IN ("awaiting-confirmation", "awaiting-email") AND user_date_gmt <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 2 DAY) ORDER BY user_id DESC';
        if (is_int($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $db->query($query);
        $users = $db->fetchAll();
        foreach ($users as $user) {
            $user = self::formatArray($user);
            self::delete($user);
        }
    }
}
