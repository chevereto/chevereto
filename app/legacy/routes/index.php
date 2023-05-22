<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\random_values;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\request;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    parse_str(server()['QUERY_STRING'] ?? '', $querystr);
    if (key($querystr) != 'random' && starts_with('route_', Settings::get('homepage_style'))) {
        $route = str_replace_first('route_', '', Settings::get('homepage_style'));
        $handler->mapRoute($route);
        $routeCallable = include PATH_APP_LEGACY_ROUTES . $route . '.php';

        return $routeCallable($handler);
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    if (server()['QUERY_STRING'] ?? false) {
        switch (key($querystr)) {
            case 'random':
                if (!$handler::cond('random_enabled')) {
                    redirect('/');
                }
                $tables = DB::getTables();
                $db = DB::getInstance();
                $db->query('SELECT MIN(image_id) as min, MAX(image_id) as max FROM ' . $tables['images']);
                $limit = $db->fetchSingle();
                $random_ids = random_values((int) $limit['min'], (int) $limit['max'], 100);
                if (count($random_ids) == 1) {
                    $random_ids[] = $random_ids[0];
                }
                if ($limit['min'] !== $limit['max']) {
                    $last_viewed_image = decodeID(session()['last_viewed_image'] ?? '');
                    if (($key = array_search($last_viewed_image, $random_ids)) !== false) {
                        unset($random_ids[$key]);
                    }
                }
                $query = 'SELECT image_id FROM ' . $tables['images'] . ' LEFT JOIN ' . $tables['albums'] . ' ON ' . $tables['images'] . '.image_album_id = ' . $tables['albums'] . '.album_id WHERE image_is_approved = 1 AND image_id IN (' . implode(',', $random_ids) . ") AND (album_privacy = 'public' OR album_privacy IS NULL) ";
                if (!getSetting('show_nsfw_in_random_mode')) {
                    if ($logged_user) {
                        $query .= 'AND (' . $tables['images'] . '.image_nsfw = 0 OR ' . $tables['images'] . '.image_user_id = ' . $logged_user['id'] . ') ';
                    } else {
                        $query .= 'AND ' . $tables['images'] . '.image_nsfw = 0 ';
                    }
                }
                if ($handler::cond('forced_private_mode')) {
                    $query .= 'AND ' . $tables['images'] . '.image_user_id = ' . $logged_user['id'] . ' ';
                }
                if (!(bool) env()['CHEVERETO_ENABLE_USERS']) {
                    $query .= 'AND ' . $tables['images'] . '.image_user_id=' . (getSetting('website_mode_personal_uid') ?? 0) . ' ';
                }
                $query .= 'ORDER BY RAND() LIMIT 1';
                $db->query($query);
                $fetch = $db->fetchSingle();
                if (!$fetch) {
                    $image = false;
                } else {
                    $imageId = (int) $fetch['image_id'];
                    $image = Image::getSingle(id: $imageId, pretty: true);
                    if (!isset($image['file_resource']['chain']['image'])) {
                        $image = false;
                    }
                }
                if (!$image) {
                    if ((session()['random_failure'] ?? 0) > 3) {
                        redirect();
                    } else {
                        sessionVar()->put('random_failure', (session()['random_failure'] ?? 0) + 1);
                    }
                } else {
                    if (isset(session()['random_failure'])) {
                        sessionVar()->remove('random_failure');
                    }
                }
                redirect(
                    $image
                        ? $image['path_viewer']
                        : '?random'
                );

                return;
            case 'v':
                if (preg_match('{^\w*\.jpg|png|gif$}', get()['v'] ?? '')) {
                    $explode = array_filter(explode('.', get()['v']));
                    if (count($explode) == 2) {
                        $image = DB::get('images', ['name' => $explode[0], 'extension' => $explode[1]], 'AND', [], 1) ?: [];
                        if ($image !== []) {
                            $image = Image::formatArray($image);
                            redirect($image['path_viewer']);
                        }
                    }
                }
                $handler->issueError(404);

                return;

                break;
            case 'list':
                $handler->setTemplate('index');

                break;
            case 'lang':
            case 'page':
            case 'seek':
            case 'peek':
                // known qs
                break;
            default:
                // ignore all
                break;
        }
    }
    if (Settings::get('homepage_style') == 'split') {
        $tabs = [
            [
                'tools' => true,
                'current' => true,
                'id' => 'home-pics',
                'type' => 'image',
            ],
        ];
        $home_uids = getSetting('homepage_uids');
        $home_uid_is_null = ($home_uids == '' || $home_uids == '0');
        $home_uid_arr = $home_uid_is_null ? false : explode(',', $home_uids);
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
        $doing = is_array($home_uid_arr) ? 'recent' : 'trending';
        $explore_semantics = $handler::var('explore_semantics');
        $list = $explore_semantics[$doing];
        $list['list'] = $doing;
        $getParams = Listing::getParams(request());
        $listing = new Listing();
        $listingParams = [
            'listing' => $doing,
            'basename' => '/',
            'params_hidden' => [
                'hide_empty' => 1,
                'hide_banned' => 1,
                'album_min_image_count' => getSetting('explore_albums_min_image_count'),
                'route' => 'index',
            ],
        ];
        $tabs = Listing::getTabs($listingParams, $getParams, true);
        $currentKey = $tabs['currentKey'];
        $currentTab = $tabs['tabs'][$currentKey];
        $type = $currentTab['type'];
        $tabs = $tabs['tabs'];
        parse_str($tabs[$currentKey]['params'], $tabs_params);
        $getParams['sort'] = explode('_', $tabs_params['sort']);
        $handler::setVar('list_params', $getParams);
        $listing->setParamsHidden($listingParams['params_hidden']);
        if (is_array($home_uid_arr)) {
            foreach ($tabs as $k => &$v) {
                if ($v['type'] == 'users') {
                    unset($tabs[$k]);
                }
            }
            $prefix = DB::getFieldPrefix($type);
            $where = 'WHERE ' . $prefix . '_user_id IN(' . $home_uid_bind . ')';
            if ($home_uid_is_null) {
                $where .= ' OR ' . $prefix . '_user_id IS NULL';
            }
            $listing->setWhere($where);
            foreach ($home_uid_arr as $k => $v) {
                $listing->bind(':user_id_' . $k, $v);
            }
        } else {
            $canonical = str_replace_first(get_base_url(), get_base_url('explore/trending'), get_current_url());
            $handler::setVar('canonical', $canonical);
        }
        $listing->setType($type);
        if (isset($getParams['reverse'])) {
            $listing->setReverse($getParams['reverse']);
        }
        if (isset($getParams['seek'])) {
            $listing->setSeek($getParams['seek']);
        }
        $listing->setOffset($getParams['offset']);
        $listing->setLimit($getParams['limit']); // how many results?
        $listing->setSortType($getParams['sort'][0]); // date | size | views | likes
        $listing->setSortOrder($getParams['sort'][1]); // asc | desc
        $listing->setRequester(Login::getUser());
        $listing->exec();
        $handler::setVar('list', $list);
        $handler::setVar('tabs', $tabs);
        $handler::setVar('listing', $listing);
    }
    $handler::setVar('doctitle', Settings::get('website_doctitle'));
    $handler::setVar('pre_doctitle', Settings::get('website_name'));
    if (isset($logged_user['is_content_manager']) && $logged_user['is_content_manager']) {
        $handler::setVar('user_items_editor', false);
    }
    $handler::setVar('share_links_array', get_share_links());

    $homepage_cta = [
        '<a',
        getSetting('homepage_cta_fn') == 'cta-upload'
            ? (
                getSetting('upload_gui') == 'js' && Handler::cond('upload_allowed')
                    ? 'data-trigger="anywhere-upload-input"'
                    : 'href="' . get_base_url('upload') . '"'
            )
            : 'href="'
                . getSetting('homepage_cta_fn_extra')
                . '"',
        (getSetting('homepage_cta_fn') == 'cta-upload' && !getSetting('guest_uploads'))
            ? 'data-login-needed="true"'
            : '',
        'class="btn btn-big accent ' . getSetting('homepage_cta_color') . '">'
        . (getSetting('homepage_cta_html')
            ?: '<i class="fas fa-cloud-upload-alt"></i><span class="btn-text">'
                . _s('Start uploading') . '</span>')
                . '</a>'
    ];
    $handler::setVar('homepage_cta', join(' ', $homepage_cta));
};
