<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use Chevere\ThrowableHandler\Documents\PlainDocument;
use function Chevere\ThrowableHandler\throwableHandler;
use function Chevere\Writer\writers;
use function Chevere\Xr\throwableHandler as XrThrowableHandler;
use Chevereto\Config\Config;
use Chevereto\Legacy\Classes\Akismet;
use Chevereto\Legacy\Classes\Album;
use Chevereto\Legacy\Classes\ApiKey;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Follow;
use Chevereto\Legacy\Classes\HybridauthSession;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Import;
use Chevereto\Legacy\Classes\IpBan;
use Chevereto\Legacy\Classes\Like;
use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Notification;
use Chevereto\Legacy\Classes\Search;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\Stat;
use Chevereto\Legacy\Classes\Storage;
use Chevereto\Legacy\Classes\TwoFactor;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\array_filter_array;
use function Chevereto\Legacy\G\check_value;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\json_output;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\isDebug;
use function Chevereto\Legacy\isShowEmbedContent;
use function Chevereto\Legacy\send_mail;
use function Chevereto\Legacy\time_elapsed_string;
use function Chevereto\Vars\files;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;
use function Chevereto\Vars\session;
use Hybridauth\Hybridauth;

return function (Handler $handler) {
    try {
        $REQUEST = request();
        $FILES = files();
        $POST = post();
        if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
            throw new Exception(_s('Request denied'), 401);
        }
        $logged_user = Login::getUser();
        $logged_user_source_db = [
            'user_name' => $logged_user['name'] ?? null,
            'user_username' => $logged_user['username'] ?? null,
            'user_email' => $logged_user['email'] ?? null,
        ];
        $doing = $REQUEST['action'];
        if ($logged_user && $logged_user['status'] !== 'valid') {
            $doing = 'deny';
        }
        if (in_array($doing, ['importStats', 'importEdit', 'importDelete'])) {
            if (Login::isAdmin() == false) {
                throw new Exception(_s('Request denied'), 403);
            }
            $import = new Import();
        }
        switch ($doing) {
            case 'upload': // EX 100
                if (!$handler::cond('upload_allowed')) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $source = $REQUEST['type'] == 'file' ? $FILES['source'] : $REQUEST['source'];
                $type = $REQUEST['type'];
                $owner_id = !empty($REQUEST['owner']) ? decodeID($REQUEST['owner']) : $logged_user['id'] ?? null;
                if (isset($REQUEST['what']) && in_array($REQUEST['what'], ['avatar', 'background'])) {
                    if ($logged_user === []) {
                        throw new Exception(_s('Login needed'), 403);
                    }
                    if (!$handler::cond('content_manager') && $owner_id != $logged_user['id']) {
                        throw new Exception('Invalid content owner request', 115);
                    }
                    $user_picture_upload = User::uploadPicture($owner_id == $logged_user['id'] ? $logged_user : $owner_id, $REQUEST['what'], $source);
                    $json_array['success'] = ['image' => $user_picture_upload, 'message' => sprintf('%s picture uploaded', ucfirst($type)), 'code' => 200];

                    break;
                }
                if ($handler::cond('forced_private_mode')) {
                    $REQUEST['privacy'] = getSetting('website_content_privacy_mode');
                }
                if (!empty($REQUEST['album_id'])) {
                    $REQUEST['album_id'] = decodeID($REQUEST['album_id']);
                }
                if (!$handler::cond('content_manager') && getSetting('akismet')) {
                    Akismet::checkImage(
                        $REQUEST['title'] ?? null,
                        $REQUEST['description'] ?? null,
                        $logged_user_source_db
                    );
                }
                $uploadToWebsite = Image::uploadToWebsite($source, $logged_user, $REQUEST);
                if ($logged_user !== []) {
                    session_write_close(); // guest session uploads
                }
                $uploaded_id = intval($uploadToWebsite[0]);
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'image uploaded', 'code' => 200];
                $image = Image::getSingle($uploaded_id);
                if ($image === []) {
                    throw new LogicException(
                        message('Missing image')
                    );
                }
                $image = Image::formatArray($image, true);
                $image['delete_url'] = Image::getDeleteUrl(encodeID($uploaded_id), $uploadToWebsite[1]);
                if (!$image['is_approved']) {
                    unset($image['image']['url'], $image['thumb']['url'], $image['medium']['url'], $image['url'], $image['display_url']);
                }
                $json_array['image'] = $image;

                break;
            case 'get-album-contents':
            case 'list': // EX 200
                if ($doing == 'get-album-contents') {
                    if (!isShowEmbedContent()) {
                        throw new Exception(_s('Request denied'), 403);
                    }
                    $list_request = 'images';
                    $aux = $REQUEST['albumid'];
                    $REQUEST = null;
                    $REQUEST['albumid'] = $aux;
                } else {
                    $list_request = $REQUEST['list'];
                }
                if (!in_array($list_request, ['images', 'albums', 'users'])) {
                    throw new Exception('Invalid list request', 100);
                }
                $output_tpl = $list_request;
                if (isset($REQUEST['params_hidden']) && is_array($REQUEST['params_hidden'])) {
                    $params_hidden = [];
                    foreach ($REQUEST['params_hidden'] as $k => $v) {
                        if (isset($REQUEST[$k])) {
                            $params_hidden[$k] = $v;
                        }
                    }
                }
                if (!empty($REQUEST['albumid'])) {
                    $album_id = decodeID($REQUEST['albumid']);
                }
                $owner_id = null;
                $where = '';
                switch ($list_request) {
                    case 'images':
                        $binds = [];
                        $where = '';
                        if (!empty($REQUEST['like_user_id'])) {
                            $where .= 'WHERE like_user_id=:image_user_id';
                            $binds[] = [
                                'param' => ':image_user_id',
                                'value' => decodeID($REQUEST['like_user_id']),
                            ];
                        }
                        if (!empty($REQUEST['follow_user_id'])) {
                            $where .= ($where == '' ? 'WHERE' : ' AND') . ' follow_user_id=:image_user_id';
                            $binds[] = [
                                'param' => ':image_user_id',
                                'value' => decodeID($REQUEST['follow_user_id']),
                            ];
                        }
                        if (!empty($REQUEST['userid'])) {
                            $owner_id = decodeID($REQUEST['userid']);
                            $where .= ($where == '' ? 'WHERE' : ' AND') . ' image_user_id=:image_user_id';
                            $binds[] = [
                                'param' => ':image_user_id',
                                'value' => $owner_id,
                            ];
                        }
                        if (isset($album_id)) {
                            $where .= ($where == '' ? 'WHERE' : ' AND') . ' image_album_id=:image_album_id';
                            $binds[] = [
                                'param' => ':image_album_id',
                                'value' => $album_id,
                            ];
                            $album = Album::getSingle($album_id);
                            if ($album['user']['id']) {
                                $owner_id = $album['user']['id'];
                            }
                            if ($album['privacy'] == 'password' && (!$handler::cond('content_manager') && $owner_id != $logged_user['id'] && !Album::checkSessionPassword($album))) {
                                throw new Exception(_s('Request denied'), 403);
                            }
                        }
                        if (!empty($REQUEST['category_id']) and is_numeric($REQUEST['category_id'])) {
                            $category = $REQUEST['category_id'];
                        }
                        if (isset($REQUEST['from'])) {
                            switch ($REQUEST['from']) {
                                case 'user':
                                    $output_tpl = 'user/images';

                                    break;
                                case 'album':
                                    $output_tpl = 'album/images';

                                    break;
                            }
                        }

                        break;
                    case 'albums':
                        $binds = [];
                        $where = '';
                        if (!empty($REQUEST['userid'])) {
                            $owner_id = decodeID($REQUEST['userid']);
                            $where .= 'WHERE album_user_id=:album_user_id';
                            $binds[] = [
                                'param' => ':album_user_id',
                                'value' => $owner_id,
                            ];
                        }
                        if (isset($REQUEST['from'])) {
                            switch ($REQUEST['from']) {
                                case 'user':
                                    $output_tpl = 'user/albums';

                                    break;
                                case 'album':
                                    $output_tpl = 'album';

                                    break;
                            }
                        }
                        if (isset($album_id)) {
                            $where .= ($where == '' ? 'WHERE' : ' AND') . ' album_parent_id=:album_id';
                            $binds[] = [
                                'param' => ':album_id',
                                'value' => $album_id,
                            ];
                        }

                        break;
                    case 'users':
                        $where = '';
                        if (getSetting('enable_followers') and (!empty($REQUEST['following_user_id']) or !empty($REQUEST['followers_user_id']))) {
                            $doing = !empty($REQUEST['following_user_id']) ? 'following' : 'followers';
                            $user_id = decodeID($doing == 'following' ? $REQUEST['following_user_id'] : $REQUEST['followers_user_id']);
                            $where = 'WHERE follow' . ($doing == 'following' ? null : '_followed') . '_user_id=:user_id';
                            $binds[] = [
                                'param' => ':user_id',
                                'value' => $user_id,
                            ];
                        }

                        break;
                }
                if (!empty($REQUEST['q'])) {
                    $search = new Search();
                    $search->q = $REQUEST['q'];
                    $search->type = $list_request;
                    $search->request = $REQUEST;
                    $search->requester = Login::getUser();
                    $search->build();
                    if (!check_value($search->q)) {
                        throw new Exception('Missing search term', 400);
                    }
                    $where .= $where == '' ? $search->wheres : preg_replace('/WHERE /', ' AND ', $search->wheres, 1);
                    $binds = array_merge($binds ?? [], $search->binds);
                }
                $getParams = Listing::getParams(request(), true);
                if ($getParams['sort'][0] == 'likes' && !getSetting('enable_likes')) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $album_fetch = 0;
                if ($doing == 'get-album-contents' && isset($album['image_count'])) {
                    $album_fetch = min(1000, $album['image_count']);
                    $getParams = [
                        'items_per_page' => $album_fetch,
                        'page' => 0,
                        'limit' => $album_fetch,
                        'offset' => 0,
                        'sort' => ['date', 'desc'],
                    ];
                }
                $listing = new Listing();
                if (array_key_exists('approved', $REQUEST)) {
                    if (Login::isAdmin() || $logged_user['is_manager']) {
                        $listing->setApproved((int) $REQUEST['approved']);
                    } else {
                        throw new Exception(_s('Request denied'), 403);
                    }
                }
                $listing->setType($list_request);
                if (isset($getParams['reverse'])) {
                    $listing->setReverse($getParams['reverse']);
                }
                if (isset($getParams['seek'])) {
                    $listing->setSeek($getParams['seek']);
                }
                $listing->setOffset($getParams['offset']);
                $listing->setLimit($getParams['limit']);
                $listing->setSortType($getParams['sort'][0]);
                $listing->setSortOrder($getParams['sort'][1]);
                if (isset($category)) {
                    $listing->setCategory($category);
                }
                $home_uids = getSetting('homepage_uids');
                if (Settings::get('homepage_style') == 'split' && isset($home_uids) && isset($POST['params_hidden']['route']) && $POST['params_hidden']['route'] == 'index') {
                    $home_uid_is_null = ($home_uids == '' or $home_uids == '0' ? true : false);
                    $home_uid_arr = !$home_uid_is_null ? explode(',', $home_uids) : false;
                    if (is_array($home_uid_arr)) {
                        $home_uid_bind = [];
                        foreach ($home_uid_arr as $k => $v) {
                            $home_uid_bind[] = ':user_id_' . $k;
                            if ($v == 0) {
                                $home_uid_is_null = true;
                            }
                        }
                        $home_uid_bind = implode(',', $home_uid_bind);
                    }
                    if (is_array($home_uid_arr)) {
                        $prefix = DB::getFieldPrefix($list_request);
                        $where = 'WHERE ' . $prefix . '_user_id IN(' . $home_uid_bind . ')';
                        if ($home_uid_is_null) {
                            $where .= ' OR ' . $prefix . '_user_id IS NULL';
                        }
                        foreach ($home_uid_arr as $k => $v) {
                            $listing->bind(':user_id_' . $k, $v);
                        }
                    }
                }
                $listing->setWhere($where);
                if (isset($owner_id)) {
                    $listing->setOwner((int) $owner_id);
                }
                $listing->setRequester($logged_user);
                if (in_array($list_request, ['images', 'albums']) && ($handler::cond('content_manager') || ($logged_user !== [] && $owner_id == $logged_user['id']))) {
                    $listing->setTools(true);
                }
                if (!empty($params_hidden)) {
                    $listing->setParamsHidden($params_hidden);
                }
                if ($list_request == 'images' && !empty($REQUEST['albumid'])) {
                    if ($handler::cond('forced_private_mode')) {
                        $album['privacy'] = getSetting('website_content_privacy_mode');
                    }
                    if (isset($album['privacy'])) {
                        $listing->setPrivacy($album['privacy']);
                    }
                }
                if (isset($binds)) {
                    foreach ($binds as $bind) {
                        $listing->bind($bind['param'], $bind['value']);
                    }
                }
                $listing->exec();
                $json_array['status_code'] = 200;
                if ($doing == 'get-album-contents'
                    && isset($album, $album['image_count'])) {
                    $json_array['album'] = array_filter_array($album, ['id', 'creation_ip', 'password', 'user', 'privacy_extra', 'privacy_notes'], 'rest');
                    $contents = [];
                    foreach ($listing->outputAssoc() as $v) {
                        $contents[] = array_filter_array($v, ['title', 'id_encoded', 'url', 'url_short', 'path_viewer', 'url_viewer', 'filename', 'medium', 'thumb'], 'exclusion');
                    }
                    $json_array['is_output_truncated'] = $album['image_count'] > $album_fetch ? 1 : 0;
                    $json_array['contents'] = $contents;
                } else {
                    $json_array['html'] = $listing->htmlOutput($output_tpl);
                }
                $json_array['seekEnd'] = $listing->seekEnd;

                break;
            case 'edit': // EX 3X
                if ($logged_user === []) {
                    throw new Exception(_s('Login needed'), 403);
                }
                $editing_request = $REQUEST['editing'];
                $editing = $editing_request;
                $type = $REQUEST['edit'];
                $owner_id = !empty($REQUEST['owner']) ? decodeID($REQUEST['owner']) : $logged_user['id'];
                if (!in_array($type, ['image', 'album', 'images', 'albums', 'category', 'storage', 'ip_ban'])) {
                    throw new Exception('Invalid edit request', 100);
                }
                if (is_null($editing['id'])) {
                    throw new Exception('Missing edit target id', 100);
                } else {
                    $id = decodeID($editing['id']);
                }
                $editing['new_album'] = isset($editing['new_album']) && $editing['new_album'] == 'true';
                $allowed_to_edit = [
                    'image' => ['category_id', 'title', 'description', 'album_id', 'nsfw'],
                    'album' => ['name', 'privacy', 'album_id', 'description', 'password'],
                    'category' => ['name', 'description', 'url_key'],
                    'storage' => ['name', 'bucket', 'region', 'url', 'server', 'capacity', 'is_https', 'is_active', 'api_id', 'key', 'secret', 'account_id', 'account_name'],
                    'ip_ban' => ['ip', 'expires', 'message'],
                ];
                $allowed_to_edit['images'] = $allowed_to_edit['image'];
                $allowed_to_edit['albums'] = $allowed_to_edit['album'];
                if ($editing['new_album']) {
                    $new_album = ['new_album', 'album_name', 'album_privacy', 'album_password', 'album_description'];
                    $allowed_to_edit['image'] = array_merge($allowed_to_edit['image'], $new_album);
                    $allowed_to_edit['album'] = array_merge($allowed_to_edit['album'], $new_album);
                }
                $editing = array_filter_array($editing, $allowed_to_edit[$type], 'exclusion');
                if ($handler::cond('forced_private_mode') and in_array($type, ['album', 'image'])) {
                    $editing[$type == 'album' ? 'privacy' : 'album_privacy'] = getSetting('website_content_privacy_mode');
                }
                if (count($editing) == 0) {
                    throw new Exception('Invalid edit request', 403);
                }
                if (isset($editing['album_id']) && $editing['album_id'] !== '') {
                    $editing['album_id'] = decodeID($editing['album_id']);
                }
                switch ($type) {
                    case 'image':
                        $source_image_db = Image::getSingle($id);
                        if ($source_image_db === []) {
                            throw new Exception("Image doesn't exists", 100);
                        }
                        if (
                            isset($editing['nsfw']) && $editing['nsfw'] != $source_image_db['image_nsfw']
                            && getSetting('image_lock_nsfw_editing')
                            && !(Login::isAdmin() || $logged_user['is_manager'])
                        ) {
                            throw new Exception('Invalid request', 403);
                        }
                        if (!$handler::cond('content_manager') && $source_image_db['image_user_id'] != $logged_user['id']) {
                            throw new Exception('Invalid content owner request', 101);
                        }
                        if (isset($editing['new_album'])) {
                            if (!$handler::cond('content_manager') && getSetting('akismet')) {
                                Akismet::checkAlbum($editing['album_name'], $editing['album_description'], $source_image_db);
                            }
                            $inserted_album = Album::insert([
                                'name' => $editing['album_name'] ?? null,
                                'user_id' => $source_image_db['image_user_id'] ?? null,
                                'privacy' => $editing['album_privacy'] ?? null,
                                'description' => $editing['album_description'] ?? null,
                                'password' => $editing['album_password'] ?? null,
                            ]);
                            $editing['album_id'] = $inserted_album;
                        }
                        if (!empty($editing['category_id']) and !array_key_exists($editing['category_id'], $handler::var('categories'))) {
                            throw new Exception('Invalid category', 102);
                        }
                        unset($editing['album_privacy'], $editing['new_album'], $editing['album_name']);
                        if (!$handler::cond('content_manager') && getSetting('akismet')) {
                            Akismet::checkImage($editing['title'], $editing['description'], $source_image_db);
                        }
                        Image::update($id, $editing);
                        $image_edit_db = Image::getSingle($id);
                        if ($image_edit_db === []) {
                            throw new LogicException(
                                message('Missing image')
                            );
                        }
                        if ($source_image_db['image_album_id'] !== $image_edit_db['image_album_id'] && $image_edit_db['image_album_id']) {
                            global $image_album_slice, $image_id;
                            $image_album_slice = Image::getAlbumSlice($id, (int) $image_edit_db['image_album_id'], 2);
                            $image_id = $image_edit_db['image_id'];
                        }
                        $album_id = $image_edit_db['image_album_id'];
                        $json_array['status_code'] = 200;
                        $json_array['success'] = ['message' => 'Image edited', 'code' => 200];
                        $json_array['editing'] = $editing_request;
                        $json_array['image'] = Image::formatArray($image_edit_db, true);
                        if (isset($image_album_slice)) {
                            // Add the album URL to the slice
                            $image_album_slice['url'] = Album::getUrl(encodeID((int) $album_id));
                            ob_start();
                            include_theme_file('snippets/image_album_slice');
                            $html = ob_get_contents();
                            ob_end_clean();
                            $json_array['image']['album']['slice'] = [
                                'next' => $image_album_slice['next']['path_viewer'] ?? '',
                                'prev' => $image_album_slice['prev']['path_viewer'] ?? '',
                                'html' => $html,
                            ];
                        } else {
                            $json_array['image']['album']['slice'] = null;
                        }

                        break;
                    case 'album':
                        $source_album_db = Album::getSingle(
                            id: $id,
                            pretty: false
                        );
                        if ($source_album_db === []) {
                            throw new Exception("Album doesn't exists", 100);
                        }
                        if (!$handler::cond('content_manager') && $source_album_db['album_user_id'] != $logged_user['id']) {
                            throw new Exception('Invalid content owner request', 102);
                        }
                        if (isset($editing['album_id']) || isset($editing['new_album'])) {
                            $album_move = true;
                            if (isset($editing['new_album'])) {
                                if (!$handler::cond('content_manager') && getSetting('akismet')) {
                                    Akismet::checkAlbum($editing['album_name'], $editing['album_description'], $source_album_db);
                                }
                                $editing['album_id'] = Album::insert([
                                    'name' => $editing['album_name'],
                                    'user_id' => $source_album_db['album_user_id'],
                                    'privacy' => $editing['album_privacy'],
                                    'description' => $editing['album_description'],
                                    'password' => $editing['album_password'],
                                ]);
                            } else {
                                if ($editing['album_id'] === '') {
                                    $editing['album_id'] = null;
                                }
                            }
                            Album::moveContents($id, $editing['album_id']);
                        } else {
                            unset($editing['album_privacy'], $editing['new_album'], $editing['album_name']);
                            if (!$handler::cond('content_manager') && getSetting('akismet')) {
                                Akismet::checkAlbum($editing['name'], $editing['description'], $source_album_db);
                            }
                            Album::update($id, $editing);
                        }
                        $album_edited = Album::getSingle((int) ($editing['album_id'] ?? $id));
                        if ($album_edited === []) {
                            throw new Exception("Edited album doesn't exists", 100);
                        }
                        $json_array['status_code'] = 200;
                        $json_array['success'] = ['message' => 'Album edited', 'code' => 200];
                        $json_array['album'] = $album_edited;
                        if (isset($album_move)) {
                            $json_array['old_album'] = Album::formatArray(
                                Album::getSingle(id: $id, pretty: false),
                                true
                            );
                            $json_array['album']['html'] = Listing::getAlbumHtml($album_edited['id'] ?? '');
                            $json_array['old_album']['html'] = Listing::getAlbumHtml($id);
                        }

                        break;
                    case 'category':
                        if (!Login::isAdmin()) {
                            throw new Exception('Invalid content owner request', 107);
                        }
                        $id = $REQUEST['editing']['id'];
                        if (!array_key_exists($id, $handler::var('categories'))) {
                            throw new Exception('Invalid target category', 100);
                        }
                        if (!isset($editing['name'])) {
                            throw new Exception('Invalid category name', 101);
                        }
                        if (!preg_match('/^[\-\w]+$/', $editing['url_key'] ?? '')) {
                            throw new Exception('Invalid category URL key', 102);
                        }
                        if (is_array($handler::var('categories'))) {
                            foreach ($handler::var('categories') as $v) {
                                if ($v['id'] == $id) {
                                    continue;
                                }
                                if ($v['url_key'] == $editing['url_key']) {
                                    $category_error = true;

                                    break;
                                }
                            }
                        }
                        if ($category_error ?? false) {
                            throw new Exception('Category URL key already being used.', 103);
                        }
                        nullify_string($editing['description']);
                        $update_category = DB::update('categories', $editing, ['id' => $id]);
                        if (!$update_category) {
                            throw new Exception('Failed to edit category', 400);
                        }
                        $category = DB::get('categories', ['id' => $id])[0];
                        $category['category_url'] = get_base_url('category/' . $category['category_url_key']);
                        $category = DB::formatRow($category);
                        $json_array['status_code'] = 200;
                        $json_array['success'] = ['message' => 'Category edited', 'code' => 200];
                        $json_array['category'] = $category;

                        break;
                    case 'ip_ban':
                        if (!$handler::cond('content_manager')) {
                            throw new Exception('Invalid content owner request', 108);
                        }
                        $id = $REQUEST['editing']['id'];
                        IpBan::validateIP($editing['ip']);
                        if (!empty($editing['expires']) and !preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $editing['expires'])) {
                            throw new Exception('Invalid expiration date format', 102);
                        }

                        try {
                            $ipAlreadyBanned = IpBan::getSingle(['ip' => $editing['ip']]);
                            if (($ipAlreadyBanned['id'] ?? 0) !== $id) {
                                throw new Exception(_s('IP address already banned'), 103);
                            }
                            if (empty($editing['expires'])) {
                                $editing['expires'] = null;
                            }
                            $editing = array_merge($editing, ['expires_gmt' => is_null($editing['expires']) ? null : gmdate('Y-m-d H:i:s', strtotime($editing['expires']))]);
                            if (!IpBan::update(['id' => $id], $editing)) {
                                throw new Exception('Failed to edit IP ban', 400);
                            }
                            $json_array['status_code'] = 200;
                            $json_array['success'] = ['message' => 'IP ban edited', 'code' => 200];
                            $json_array['ip_ban'] = IpBan::getSingle(['id' => $id]);
                        } catch (Exception $throwable) {
                            $json_array = [
                                'status_code' => 403,
                                'error' => ['message' => $throwable->getMessage(), $throwable->getCode()],
                            ];

                            break;
                        }

                        break;
                    case 'storage':
                        if (!Login::isAdmin()) {
                            throw new Exception('Invalid content owner request', 109);
                        }
                        $id = (int) $REQUEST['editing']['id'];
                        Storage::update($id, $editing);
                        $storage = Storage::getSingle($id);
                        $json_array['status_code'] = 200;
                        $json_array['success'] = ['message' => 'Storage edited', 'code' => 200];
                        $json_array['storage'] = $storage;

                        break;
                }

                break;
            case 'add-user':
                if (!Login::isAdmin()) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $user = $REQUEST['user'];
                foreach (['username', 'email', 'password', 'role'] as $v) {
                    if ($user[$v] == '') {
                        throw new Exception(_s('Missing values'), 100);
                    }
                }
                if (!User::isValidUsername($user['username'])) {
                    throw new Exception(_s('Invalid username'), 101);
                }
                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception(_s('Invalid email'), 102);
                }
                if (!preg_match('/' . getSetting('user_password_pattern') . '/', $user['password'] ?? '')) {
                    throw new Exception(_s('Invalid password'), 103);
                }
                if (!in_array($user['role'], ['user', 'manager', 'admin'])) {
                    throw new Exception(_s('Invalid role'), 104);
                }
                if (DB::get('users', ['username' => $user['username']])) {
                    throw new Exception(_s('Username already being used'), 200);
                }
                if (DB::get('users', ['email' => $user['email']])) {
                    throw new Exception(_s('Email already being used'), 200);
                }
                $is_manager = 0;
                $is_admin = 0;
                switch ($user['role']) {
                    case 'manager':
                        $is_manager = 1;

                        break;
                    case 'admin':
                        $is_admin = 1;

                        break;
                }
                $add_user = User::insert([
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'is_admin' => $is_admin,
                    'is_manager' => $is_manager,
                ]);
                if ($add_user) {
                    Login::addPassword($add_user, $user['password'], false);
                }
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'User added', 'code' => 200];

                break;
            case 'add-category':
                if (!Login::isAdmin()) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $category = $REQUEST['category'];
                $category_error = false;
                foreach (['name', 'url_key'] as $v) {
                    if ($category[$v] == '') {
                        throw new Exception(_s('Missing values'), 100);
                    }
                }
                if (!preg_match('/^[-\w]+$/', $category['url_key'] ?? '')) {
                    throw new Exception('Invalid category URL key', 102);
                }
                if ($handler::var('categories')) {
                    foreach ($handler::var('categories') as $v) {
                        if ($v['url_key'] == $category['url_key']) {
                            $category_error = true;

                            break;
                        }
                    }
                }
                if ($category_error) {
                    throw new Exception('Category URL key already being used.', 103);
                }
                nullify_string($category['description']);
                $category = array_filter_array($category, ['name', 'url_key', 'description'], 'exclusion');
                $add_category = DB::insert('categories', $category);
                $category = DB::get('categories', ['id' => $add_category])[0];
                $category['category_url'] = get_base_url('category/' . $category['category_url_key']);
                $category = DB::formatRow($category);
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'Category added', 'code' => 200];
                $json_array['category'] = $category;

                break;
            case 'add-ip_ban':
                if (!$handler::cond('content_manager')) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $ip_ban = array_filter_array($REQUEST['ip_ban'], ['ip', 'expires', 'message'], 'exclusion');
                IpBan::validateIP($ip_ban['ip']);
                if (!empty($ip_ban['expires']) and !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $ip_ban['expires'])) {
                    throw new Exception('Invalid expiration date format', 102);
                }

                try {
                    if (IpBan::getSingle(['ip' => $ip_ban['ip']]) !== []) {
                        throw new Exception(_s('IP address already banned'), 103);
                    }
                    if (empty($ip_ban['expires'])) {
                        $ip_ban['expires'] = null;
                    }
                    $ip_ban = array_merge($ip_ban, ['date' => datetime(), 'date_gmt' => datetimegmt(), 'expires_gmt' => is_null($ip_ban['expires']) ? null : gmdate('Y-m-d H:i:s', strtotime($ip_ban['expires']))]);
                    $add_ip_ban = IpBan::insert($ip_ban);
                } catch (Exception $throwable) {
                    $json_array = [
                        'status_code' => 403,
                        'error' => ['message' => $throwable->getMessage(), $throwable->getCode()],
                    ];

                    break;
                }
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'IP ban added', 'code' => 200];
                $json_array['ip_ban'] = IpBan::getSingle(['id' => $add_ip_ban]);

                break;
            case 'add-storage':
                if (!Login::isAdmin()) {
                    throw new Exception(_s('Request denied'), 403);
                }
                $storage = $REQUEST['storage'];
                $add_storage = Storage::insert($storage);
                $storage = Storage::getSingle($add_storage);
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'Storage added', 'code' => 200];
                $json_array['storage'] = $storage;

                break;
            case 'edit-category':
            case 'flag-safe':
            case 'flag-unsafe':
                if ($logged_user === []) {
                    throw new Exception(_s('Login needed'), 403);
                }
                $editing = $REQUEST['editing'];
                $owner_id = $logged_user['id'];
                // Admin
                if (!$handler::cond('content_manager') and $owner_id != $logged_user['id']) {
                    throw new Exception('Invalid content owner request', 110);
                }
                $ids = [];
                foreach ($editing['ids'] as $id) {
                    $ids[] = decodeID($id);
                }
                $images = Image::getMultiple($ids);
                $images_ids = [];
                foreach ($images as $image) {
                    if (!$handler::cond('content_manager') and $image['image_user_id'] != $logged_user['id']) {
                        continue;
                    }
                    $images_ids[] = $image['image_id'];
                }
                if (!$images_ids) {
                    throw new Exception('Invalid content owner request', 111);
                }
                $prop = null;
                $message = '';
                switch ($doing) {
                    case 'flag-safe':
                    case 'flag-unsafe':
                        if (getSetting('image_lock_nsfw_editing')
                            && !(Login::isAdmin() || $logged_user['is_manager'])
                        ) {
                            throw new Exception('Invalid request', 403);
                        }
                        $query_field = 'nsfw';
                        $prop = intval($editing['nsfw'] == 1);
                        $message = 'Content flag changed';

                        break;
                    case 'edit-category':
                        $query_field = 'category_id';
                        $prop = $editing['category_id'] ?: null;
                        $message = 'Content category edited';

                        break;
                }
                if (!isset($query_field)) {
                    throw new Exception('Invalid request', 403);
                }
                $db = DB::getInstance();
                $db->query('UPDATE `' . DB::getTable('images') . '` SET `image_' . $query_field . '`=:prop WHERE `image_id` IN (' . implode(',', $images_ids) . ')');
                $db->bind(':prop', $prop);
                $db->exec();
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => $message, 'code' => 200];
                if ($query_field == 'category_id') {
                    $json_array['category_id'] = $prop;
                }

                break;
            case 'move':
            case 'create-album':
                $type = $REQUEST['type'];
                if (!in_array($type, ['images', 'album', 'albums'])) {
                    throw new Exception('Invalid album ' . ($doing == 'move' ? 'move' : 'create') . ' request', 100);
                }
                $album = $REQUEST['album'];
                $album['new'] = $album['new'] == 'true';
                if ($logged_user === [] && $album['new'] == false) {
                    throw new Exception('Invalid request', 403);
                }
                $owner_id = !empty($REQUEST['owner'])
                    ? decodeID($REQUEST['owner'])
                    : ($logged_user['id'] ?? null);
                if (!$handler::cond('content_manager') && $owner_id != ($logged_user['id'] ?? null)) {
                    throw new Exception('Invalide content owner request' . var_export($owner_id, true), 112);
                }
                if ($handler::cond('forced_private_mode')) {
                    $album['privacy'] = getSetting('website_content_privacy_mode');
                }
                if (!$handler::cond('content_manager') && getSetting('akismet') && $album['new']) {
                    Akismet::checkAlbum($album['name'], $album['description'], $owner_id == $logged_user['id'] ? $logged_user_source_db : null);
                }
                $album_id = $album['new']
                    ? Album::insert([
                        'name' => $album['name'],
                        'user_id' => $owner_id,
                        'privacy' => $album['privacy'],
                        'description' => $album['description'],
                        'password' => $album['password'] ?? null,
                        'parent_id' => isset($album['parent_id'])
                            ? decodeID($album['parent_id'])
                            : null,
                    ])
                    : decodeID($album['id']);
                $album_db = Album::getSingle(id: $album_id, pretty: false);
                if (isset($album['ids']) && is_array($album['ids'])) {
                    if (count($album['ids']) == 0) {
                        throw new Exception('Invalid source album ids ' . ($doing == 'move' ? 'move' : 'create') . ' request', 100);
                    }
                    $ids = [];
                    foreach ($album['ids'] as $id) {
                        $ids[] = decodeID($id);
                    }
                }
                if (!empty($ids) && is_array($ids)) {
                    if ($type == 'images') {
                        $images = Image::getMultiple($ids);
                        $images_ids = [];
                        foreach ($images as $image) {
                            if ($logged_user === [] && in_array($image['image_id'], session()['guest_images'] ?? []) == false) {
                                continue;
                            }
                            if (!$handler::cond('content_manager') && $image['image_user_id'] != ($logged_user['id'] ?? null)) {
                                continue;
                            }
                            $images_ids[] = $image['image_id'];
                        }
                        if (!$images_ids) {
                            throw new Exception('Invalid content owner request', 104);
                        }
                        Album::addImages(
                            $album_db === []
                                ? null
                                : (int) $album_db['album_id'],
                            $images_ids
                        );
                    } else {
                        $album_move = true;
                        $albums = Album::getMultiple($ids);
                        $albums_ids = [];
                        foreach ($albums as $album) {
                            if (!$handler::cond('content_manager') && $album['album_user_id'] != $logged_user['id']) {
                                continue;
                            }
                            $albums_ids[] = $album['album_id'];
                        }
                        if (!$albums_ids) {
                            throw new Exception('Invalid content owner request', 105);
                        }
                        Album::moveContents($albums_ids, $album_id);
                    }
                }
                $album_move_db = isset($album_db['album_id'])
                    ? Album::getSingle(id: (int) $album_db['album_id'], pretty: false)
                    : User::getStreamAlbum($owner_id);
                $json_array['status_code'] = 200;
                $json_array['success'] = ['message' => 'Content added to album', 'code' => 200];
                if ($album_move_db !== []) {
                    $json_array['album'] = Album::formatArray($album_move_db, true);
                    $json_array['album']['html'] = Listing::getAlbumHtml($album_move_db['album_id']);
                }
                if ($type == 'albums') {
                    $json_array['albums_old'] = [];
                    foreach ($ids ?? [] as $album_id) {
                        $album_id = (int) $album_id;
                        $album_item = Album::formatArray(
                            Album::getSingle(id: $album_id, pretty: false),
                            true
                        );
                        $album_item['html'] = Listing::getAlbumHtml($album_id);
                        $json_array['albums_old'][] = $album_item;
                    }
                }

                break;
            case 'delete':
                if ($logged_user === []) {
                    throw new Exception(_s('Login needed'), 403);
                }
                $deleting = $REQUEST['deleting'] ?? null;
                $type = $REQUEST['delete'] ?? null;
                if (is_null($type)) {
                    throw new Exception('Invalid delete request', 100);
                }
                if (!$handler::cond('content_manager') && !getSetting('enable_user_content_delete') && (starts_with('image', $type) || starts_with('album', $type))) {
                    throw new Exception('Forbidden action', 403);
                }
                $owner_id = isset($REQUEST['owner']) ? decodeID($REQUEST['owner']) : $logged_user['id'];
                $multiple = ($REQUEST['multiple'] ?? null) == 'true';
                $single = ($REQUEST['single'] ?? null) == 'true';
                if (!$multiple) {
                    $single = true;
                }
                if (
                    in_array($type, ['avatar', 'background', 'user', 'category', 'ip_ban', 'api_key', 'two_factor'])
                    && !$handler::cond('content_manager') && $owner_id != $logged_user['id']
                ) {
                    throw new Exception('Invalid content owner request', 113);
                }
                if (in_array($type, ['avatar', 'background'])) {
                    User::deletePicture($owner_id == $logged_user['id'] ? $logged_user : $owner_id, $type);
                    $json_array['status_code'] = 200;
                    $json_array['success'] = ['message' => 'Profile background deleted', 'code' => 200];

                    break;
                }
                if ($type === 'two_factor') {
                    $userTarget = intval(
                        $owner_id == $logged_user['id']
                            ? $logged_user['id']
                            : $owner_id
                    );
                    if (!TwoFactor::hasFor($userTarget)) {
                        $status_code = 403;
                        $message = 'Two-factor not enabled';
                    } else {
                        TwoFactor::delete($userTarget);
                        $status_code = 200;
                        $message = 'Two-factor deleted';
                    }
                    $json_array['status_code'] = $status_code;
                    $json_array['success'] = ['message' => $message, 'code' => $status_code];

                    break;
                }
                if ($type === 'api_key') {
                    $userTarget = intval(
                        $owner_id == $logged_user['id']
                            ? $logged_user['id']
                            : $owner_id
                    );
                    $apiKey = ApiKey::getUserKey($userTarget);
                    if ($apiKey !== []) {
                        ApiKey::remove(intval($apiKey['id']));
                    }
                    $json_array['status_code'] = 200;
                    $json_array['success'] = ['message' => 'API key deleted', 'code' => 200];

                    break;
                }
                if ($type == 'user') {
                    $delete_user_id = $owner_id == $logged_user['id'] ? $logged_user : $owner_id;
                    $delete_user = User::getSingle($delete_user_id, 'id');
                    if ($delete_user === []) {
                        throw new Exception('User not found', 100);
                    }
                    if ($delete_user['is_content_manager'] && Login::isAdmin() == false) {
                        throw new Exception("Can't touch this!", 666);
                    }
                    User::delete($delete_user_id);

                    break;
                }
                if ($single) {
                    if (is_null($deleting['id'] ?? null)) {
                        throw new Exception('Missing delete target id', 100);
                    }
                } else {
                    if (is_array($deleting['ids']) && count($deleting['ids']) == 0) {
                        throw new Exception('Missing delete target ids', 100);
                    }
                }
                if ($type == 'category') {
                    if (!array_key_exists($deleting['id'], $handler::var('categories'))) {
                        throw new Exception('Invalid target category', 100);
                    }
                    $delete_category = DB::delete('categories', ['id' => $deleting['id']]);
                    if ($delete_category) {
                        $update_images = DB::update('images', ['category_id' => null], ['category_id' => $deleting['id']]);
                    } else {
                        throw new Exception('Error deleting category', 400);
                    }

                    break;
                }
                if ($type == 'ip_ban') {
                    if (!IpBan::delete(['id' => $deleting['id']])) {
                        throw new Exception('Error deleting IP ban', 400);
                    }

                    break;
                }
                if (!in_array($type, ['image', 'album', 'images', 'albums'])) {
                    throw new Exception('Invalid delete request', 100);
                }
                $db_field_prefix = in_array($type, ['image', 'images']) ? 'image' : 'album';
                switch ($type) {
                    case 'image':
                    case 'images':
                        $Class_fn = Image::class;

                        break;
                    case 'album':
                    case 'albums':
                        $Class_fn = Album::class;

                        break;
                }
                if (!isset($Class_fn)) {
                    throw new Exception('Invalid delete request', 100);
                }
                if ($single) {
                    if (is_null($deleting['id'])) {
                        throw new Exception('Missing delete target id', 100);
                    } else {
                        $id = decodeID($deleting['id']);
                    }
                    $content_db = $Class_fn::getSingle($id, false, false);
                    if ($content_db) {
                        if (!$handler::cond('content_manager') and $content_db[$db_field_prefix . '_user_id'] != $logged_user['id']) {
                            throw new Exception('Invalid content owner request', 114);
                        }
                        $delete = $Class_fn::delete($id);
                    } else {
                        throw new Exception("Content doesn't exists", 100);
                    }
                    $affected = $delete;
                } else {
                    if (!is_array($deleting['ids'])) {
                        throw new Exception('Expecting ids array values, ' . gettype($deleting['ids']) . ' given', 100);
                    }
                    $ids = [];
                    if (count($deleting['ids']) > 0) {
                        foreach ($deleting['ids'] as $id) {
                            $ids[] = decodeID($id);
                        }
                    }
                    $contents_db = $Class_fn::getMultiple($ids);
                    $owned_ids = [];
                    foreach ($contents_db as $content_db) {
                        if (!$handler::cond('content_manager') and $content_db[$db_field_prefix . '_user_id'] != $logged_user['id']) {
                            continue;
                        }
                        if (isset($content_db[$db_field_prefix . '_id'])) {
                            $owned_ids[] = $content_db[$db_field_prefix . '_id'];
                        }
                    }
                    if (!$owned_ids) {
                        throw new Exception('Invalid content owner request', 106);
                    }
                    $delete = $Class_fn::deleteMultiple($owned_ids);
                    $affected = $delete;
                }
                $json_array['success'] = [
                    'message' => ucfirst($type) . ' deleted',
                    'code' => 200,
                    'affected' => $affected,
                ];

                break;
            case 'disconnect':
                if ($logged_user === []) {
                    throw new Exception(_s('Login needed'), 403);
                }
                $disconnect = strtolower($REQUEST['disconnect']);
                $disconnect_label = ucfirst($disconnect);
                $user_id = $REQUEST['user_id']
                    ? decodeID($REQUEST['user_id'])
                    : null; // Optional param (allow admin to disconnect any user)
                if (!Login::isAdmin() && $user_id && $user_id != $logged_user['id']) {
                    throw new Exception('Invalid request', 403);
                }
                $user = !$user_id ? $logged_user : User::getSingle($user_id, 'id');
                $login_connection = $user['login'][$disconnect] ?? false;
                $providersEnabled = Login::getProviders('enabled');
                if (!array_key_exists($disconnect, $providersEnabled)) {
                    throw new Exception('Invalid disconnect value', 10);
                }
                if (!$login_connection) {
                    throw new Exception("Login connection doesn't exists", 11);
                }
                if ($user['connections_count'] == 1 && !Login::hasPassword($user_id)) {
                    throw new Exception(_s('Add a password or another social connection before deleting %s', $disconnect_label), 12);
                }
                $user_social_conn = 0;
                foreach (array_keys($providersEnabled) as $k) {
                    if (array_key_exists($k, $user['login'])) {
                        ++$user_social_conn;
                    }
                }
                if ($user_social_conn == 1
                    && Login::hasPassword($user['id'])) {
                    if (getSetting('require_user_email_confirmation')
                        && !$user['email']) {
                        throw new Exception(_s('Add an email or another social connection before deleting %s', $disconnect_label), 12);
                    }
                }
                $loginCookie = 'cookie_' . $disconnect;
                Login::deleteCookies($loginCookie, ['user_id' => $user['id']]);
                $delete_connection = Login::deleteConnection($disconnect, $user['id']);
                if ($delete_connection) {
                    if (in_array($disconnect, ['twitter', 'facebook'])) {
                        User::update($user['id'], [$disconnect . '_username' => null]);
                    }
                    $json_array['success'] = [
                        'message' => _s('%s has been disconnected.', $disconnect_label),
                        'code' => 200,
                        'redirect' => '',
                    ];
                    if ($loginCookie === Login::getSession()['type']) {
                        $config = [
                            'callback' => get_public_url('connect/' . $disconnect) . '/',
                            'providers' => [],
                        ];
                        $config['providers'][$disconnect] = [
                            'enabled' => $providersEnabled[$disconnect]['is_enabled'],
                            'keys' => [
                                'id' => $providersEnabled[$disconnect]['key_id'],
                                'secret' => $providersEnabled[$disconnect]['key_secret'],
                            ]
                        ];
                        $session = new HybridauthSession();
                        $hybridauth = new Hybridauth(config: $config, storage: $session);
                        $adapter = $hybridauth->getAdapter($disconnect);
                        if ($adapter->isConnected()) {
                            $adapter->disconnect();
                        }
                        $session->clear();
                        $json_array['success']['redirect'] = get_base_url('login');
                    }
                } else {
                    throw new Exception('Error deleting connection', 666);
                }

                break;
            case 'rebuildStats':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                Stat::rebuildTotals();
                $json_array['success'] = [
                    'message' => 'OK',
                    'code' => 200,
                    'redirURL' => get_base_url('dashboard')
                ];

                break;
            case 'testEmail':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                $send_email = send_mail($REQUEST['email'], _s('Test email from %s @ %t', ['%s' => getSetting('website_name'), '%t' => datetime()]), '<p>' . _s('This is just a test') . '</p>');
                if ($send_email) {
                    $json_array['success'] = [
                        'message' => _s('Test email sent to %s.', $REQUEST['email']),
                        'code' => 200,
                    ];
                } else {
                    $json_array['error'] = [
                        'code' => 500,
                    ];
                }

                break;
            case 'encodeId':
            case 'decodeId':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                if ($REQUEST['id'] == null) {
                    throw new Exception('Invalid request', 100);
                }
                $thing = str_replace('Id', '', $doing);
                $id = $REQUEST['id'];
                if ($thing === 'encode') {
                    $res = encodeID((int) $id);
                } else {
                    $res = decodeID($id);
                }
                $json_array['success'] = [
                    'message' => $id . ' == ' . $res,
                    'code' => 200,
                    $thing => $res,
                ];

                break;
            case 'exportUser':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                // Validate id
                if ($REQUEST['username'] == null) {
                    throw new Exception(_s('Invalid username'), 100);
                }
                $user = User::getSingle($REQUEST['username'], 'username', false);
                if ($user == false) {
                    throw new Exception(_s('Invalid username'), 101);
                }
                $user = DB::formatRow($user);
                if (!isset($REQUEST['download'])) {
                    $json_array['success'] = [
                        'message' => _s('Downloading %s data', "'" . $user['username'] . "'"),
                        'code' => 200,
                        'redirURL' => get_current_url() . '&action=exportUser&download=1',
                    ];
                } else {
                    $filename = $user['username'] . '.json';
                    $user = array_filter_array($user, ['name', 'username', 'email', 'facebook_username', 'twitter_username', 'website', 'bio', 'timezone', 'language', 'is_private', 'newsletter_subscribe']);
                    $user = json_encode($user, JSON_PRETTY_PRINT);
                    header('Content-type: application/json');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Last-Modified: ' . datetimegmt('D, d M Y H:i:s') . ' UTC');
                    header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
                    header('Pragma: anytextexeptno-cache', true);
                    header('Cache-control: private', false);
                    header('Expires: 0');
                    echo $user;
                    die();
                }

                break;
            case 'follow':
            case 'unfollow':
                if ($logged_user === []
                    || !getSetting('enable_followers')
                    || $logged_user['is_private'] == 1
                ) {
                    throw new Exception('Invalid request', 403);
                }
                $follow_array = [
                    'user_id' => $logged_user['id'],
                    'followed_user_id' => decodeID($REQUEST[$doing]['id']),
                ];
                $return = $doing == 'follow'
                    ? Follow::insert($follow_array)
                    : Follow::delete($follow_array);
                if ($return) {
                    unset($return['id']);
                    $json_array['success'] = [
                        'message' => $doing == 'follow' ? _s('User %s followed', $return['username']) : _s('User %s unfollowed', $return['username']),
                        'code' => 200,
                    ];
                    $json_array['user_followed'] = $return;
                }

                break;
            case 'album-cover-set':
            case 'album-cover-unset':
                if ($logged_user === []) {
                    throw new Exception('Invalid request', 403);
                }
                $image_pub_id = $POST[$doing]['image_id'];
                $album_pub_id = $POST[$doing]['album_id'];
                $image_id = decodeID($image_pub_id);
                $album_id = decodeID($album_pub_id);
                $image = Image::getSingle(id: $image_id, pretty: true);
                if ($image === []) {
                    throw new LogicException(
                        message('Missing image')
                    );
                }
                $album = Album::getSingle($album_id);
                if ($image['album']['id'] !== ($album['id'] ?? 0)) {
                    throw new Exception("Image doesn't belong to this album", 100);
                }
                if (isset($logged_user['id'])) {
                    $isLoggedOwner = ($image['user']['id'] ?? null) == $logged_user['id']
                        || ($album['user']['id'] ?? null) == $logged_user['id'];
                } else {
                    $isLoggedOwner = false;
                }
                if (!$handler::cond('content_manager') && !$isLoggedOwner) {
                    throw new Exception('Invalid content owner request', 101);
                }
                Album::update($album_id, ['cover_id' => $doing == 'album-cover-unset' ? null : $image_id]);
                $json_array['success'] = [
                    'message' => _s('Album cover altered'),
                    'code' => 200,
                ];

                break;
            case 'like':
            case 'dislike':
                if ($logged_user === [] || !getSetting('enable_likes')) {
                    throw new Exception('Invalid request', 403);
                }
                $like_array = [
                    'user_id' => $logged_user['id'],
                    'content_id' => decodeID($REQUEST[$doing]['id']),
                    'content_type' => $REQUEST[$doing]['object'],
                ];
                $return = $doing == 'like' ? Like::insert($like_array) : Like::delete($like_array);
                if ($return) {
                    $return['id_encoded'] = encodeID((int) $return['id']);
                    unset($return['id']);
                    $json_array['success'] = [
                        'message' => $doing == 'like' ? _s('Content liked', $return['content']['id_encoded'] ?? '') : _s('Content disliked', $return['content']['id_encoded'] ?? ''),
                        'code' => 200,
                    ];
                    $json_array['content'] = $return;
                }

                break;
            case 'regenStorageStats':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                $res = Storage::regenStorageStats($REQUEST['storageId']);
                $json_array['success'] = [
                    'message' => $res,
                    'code' => 200,
                ];

                break;
            case 'migrateStorage':
                if (!Login::isAdmin()) {
                    throw new Exception('Invalid request', 403);
                }
                $res = Storage::migrateStorage($REQUEST['sourceStorageId'], $REQUEST['targetStorageId']);
                $json_array['success'] = [
                    'message' => $res,
                    'code' => 200,
                ];

                break;
            case 'notifications':
                if ($logged_user === []) {
                    throw new Exception('Invalid request', 403);
                }
                $notification_array = [
                    'user_id' => $logged_user['id'],
                ];
                $notifications = Notification::get($notification_array);
                Notification::markAsRead($notification_array);
                $json_array['status_code'] = 200;
                if ($notifications !== []) {
                    $json_array['html'] = '';
                    $template = '<li%class>%avatar<span class="notification-text">%message</span><span class="how-long-ago">%how_long_ago</span></li>';
                    $avatar_src_tpl = [
                        0 => '<span class="user-image default-user-image"><span class="icon fas fa-user-circle"></span></span>',
                        1 => '<img class="user-image" src="%user_avatar_url" alt="%user_name_short_html">',
                    ];
                    $avatar_tpl = [
                        0 => $avatar_src_tpl[0],
                        1 => '<a href="%user_url">%user_avatar</a>',
                    ];
                    foreach ($notifications as $k => $v) {
                        $content_type = $v['content_type'];
                        switch ($v['type']) {
                            case 'like':
                                $message = _s('%u liked your %t %c', [
                                    '%t' => _s($content_type),
                                    '%c' => '<a href="' . $v[$content_type]['url_short'] . '">' . $v[$content_type][($content_type == 'image' ? 'title' : 'name') . '_truncated_html'] . '</a>',
                                ]);

                                break;
                            case 'follow':
                                $message = _s('%u is now following you');

                                break;
                        }
                        if (!isset($v['user']['id'])) {
                            continue;
                        }
                        $v['message'] = strtr($message ?? '', [
                            '%u' => $v['user']['is_private'] == 1
                                ? _s('A private user')
                                : ('<a href="' . $v['user']['url'] . '">' . $v['user']['name_short_html'] . '</a>'),
                        ]);
                        if ($v['user']['is_private'] == 1) {
                            $avatar = $avatar_tpl[0];
                        } else {
                            $avatar = strtr($avatar_tpl[1], [
                                '%user_url' => $v['user']['url'],
                                '%user_avatar' => strtr($avatar_src_tpl[isset($v['user']['avatar']) ? 1 : 0], [
                                    '%user_avatar_url' => $v['user']['avatar']['url'] ?? '',
                                    '%user_name_short_html' => $v['user']['name_short_html'],
                                ]),
                            ]);
                        }
                        $json_array['html'] .= strtr($template, [
                            '%class' => !$v['is_read'] ? ' class="new"' : null,
                            '%avatar' => $avatar,
                            '%user_url' => $v['user']['url'],
                            '%message' => $v['message'],
                            '%how_long_ago' => time_elapsed_string($v['date_gmt']),
                        ]);
                    }
                    unset($content_type);
                } else {
                    $json_array['html'] = null;
                }

                break;
            case 'importStats':
            case 'importEdit':
            case 'importDelete':
            case 'importReset':
            case 'importResume':
                if ($REQUEST['id'] == false) {
                    throw new Exception('Missing id parameter', 100);
                }
                $import->id = (int) $REQUEST['id'];
                $import->get();

                break;
            case 'paletteSet':
                if ($logged_user === []) {
                    throw new Exception('Invalid request', 403);
                }
                $palette_id = (int) $REQUEST['palette_id'];
                User::update($logged_user['id'], ['palette_id' => $palette_id]);
                $json_array['status_code'] = 200;
                $logged_user = User::getSingle($logged_user['id']);
                $json_array['palette_id'] = (int) $logged_user['palette_id'];

                break;
            case 'approve':
                if (!(Login::isAdmin() || $logged_user['is_manager'])) {
                    throw new Exception('Invalid request', 403);
                }
                $approve_ids = [];
                $approving = $REQUEST['approving'];
                if (($REQUEST['multiple'] ?? null) == 'true') {
                    $approve_ids = $approving['ids'];
                } else {
                    $approve_ids = [$approving['id']];
                }
                if ($approve_ids == []) {
                    throw new Exception('Missing approve target ids', 600);
                }
                $ids = [];
                foreach ($approve_ids as $value) {
                    $ids[] = decodeID($value);
                }
                $affected = DB::queryExecute(sprintf('UPDATE ' . DB::getTable('images') . ' SET image_is_approved = 1 WHERE image_id IN (%s)', implode(',', $ids)));
                $json_array['status_code'] = 200;
                $json_array['affected'] = $affected;

            break;
            case 'user_ban':
            case 'user_unban':
                if (!$handler::cond('content_manager')) {
                    throw new Exception('Invalid content owner request', 108);
                }
                $user_id = decodeID($REQUEST[$doing]['user_id'] ?? '');
                if ($user_id === 0) {
                    throw new Exception('Invalid user id', 109);
                }
                $user = User::getSingle($user_id);
                if ($user === []) {
                    throw new Exception('User not found', 404);
                }
                User::update($user_id, ['status' => $doing == 'user_ban' ? 'banned' : 'valid']);
                $json_array['status_code'] = 200;

                break;
            case 'deny':
                throw new Exception(_s('Request denied'), 403);
            default: // EX X
                throw new Exception(
                    !check_value($doing)
                        ? 'empty action'
                        : "invalid action $doing",
                );
        }
        if (isset($import->id)) {
            switch ($doing) {
                case 'importStats':
                    $json_array['status_code'] = 200;
                    $json_array['import'] = $import->parsedImport;

                    break;
                case 'importEdit':
                    if ($REQUEST['values'] == false) {
                        throw new Exception('Missing values parameter', 101);
                    }
                    if (is_array($REQUEST['values']) == false) {
                        throw new Exception('Expecting array values', 102);
                    }
                    $import->edit($REQUEST['values']);
                    $import->get();
                    $json_array['import'] = $import->parsedImport;
                    $json_array['status_code'] = 200;

                    break;
                case 'importReset':
                    $import->reset();
                    $json_array['import'] = $import->parsedImport;
                    $json_array['status_code'] = 200;

                    break;
                case 'importResume':
                    $import->resume();
                    $json_array['import'] = $import->parsedImport;
                    $json_array['status_code'] = 200;

                    break;
                case 'importDelete':
                    $import->delete();
                    $json_array['status_code'] = 200;
                    $json_array['import'] = $import->parsedImport;

                    break;
            }
        }
        if (isset($json_array['success']) and !isset($json_array['status_code'])) {
            $json_array['status_code'] = 200;
        }
        $json_array['request'] = $REQUEST;
    } catch (Throwable $throwable) {
        $throwableHandler = throwableHandler($throwable);
        $docInternal = new PlainDocument($throwableHandler);
        if ($throwable->getCode() < 100 || $throwable->getCode() >= 500) {
            writers()->error()
                ->write($docInternal->__toString() . "\n\n");
            XrThrowableHandler(
                $throwable,
                <<<HTML
                <div class="throwable-message">Incident ID: {$throwableHandler->id()}</div>
                HTML
            );
        }
        $message = $throwable->getMessage() . ' (' . $throwable->getCode() . ')';
        $debugLevel = Config::system()->debugLevel();
        $errorCanSurface = $throwable->getCode() === 999
            || ($throwable->getCode() > 99 && $throwable->getCode() < 600);
        $isDebug = in_array($debugLevel, [2, 3]) || isDebug();
        if (!$isDebug && !$errorCanSurface) {
            $message = '⭕️ '
                . _s('Something went wrong')
                . ' • '
                . strtr(
                    'Incident ID:%id%',
                    ['%id%' => '' . $throwableHandler->id()]
                );
        }

        $json_array = [
            'status_code' => 500,
            'error' => [
                'message' => $message,
                'type' => $throwable::class,
                'time' => $throwableHandler->dateTimeUtc()
                    ->format(DateTimeInterface::ATOM),
                'code' => $throwable->getCode(),
                'id' => $throwableHandler->id(),
            ]
        ];
    }
    json_output($json_array);
};
