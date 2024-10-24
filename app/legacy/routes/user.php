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
use Chevereto\Legacy\Classes\Follow;
use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Tag;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\headersNoCache;
use function Chevereto\Legacy\virtualRouteHandleRedirect;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\request;
use function Chevereto\Vars\server;

return function (Handler $handler) {
    $currentUrl = get_current_url();
    virtualRouteHandleRedirect('user', $handler->requestArray()[0]);
    $userIndex = (getSetting('root_route') === 'user'
        || getSetting('website_mode') === 'personal')
            ? 0
            : 1;
    if ($handler->isRequestLevel($handler::cond('mapped_route') ? 4 : 5)) {
        $handler->issueError(404);

        return;
    }
    $request_handle = $userIndex === 0
        ? $handler->requestArray()
        : $handler->request();
    $userMapPaths = ['search', 'following', 'followers'];
    $userMapPaths[] = getSetting('user_profile_view') === 'files'
        ? 'albums'
        : 'files';
    if (getSetting('website_mode') === 'personal'
        && getSetting('website_mode_personal_routing') === '/'
        && $request_handle[0] !== '/'
    ) {
        if (! in_array($request_handle[0], $userMapPaths, true)) {
            $handler->issueError(404);

            return;
        }
        $personal_mode_user = User::getSingle(getSetting('website_mode_personal_uid'));
        if ($personal_mode_user !== []) {
            $request_handle = [
                0 => $personal_mode_user['username'],
                1 => $request_handle[0],
            ];
        }
    }
    if (($request_handle[0] ?? false) === getSetting('route_user')
        && getSetting('root_route') !== 'user'
    ) {
        array_shift($request_handle);
    }
    $username = $request_handle[0] ?? null;
    if ($handler::cond('mapped_route') && $handler::mappedArgs() !== []) {
        $mapped_args = $handler::mappedArgs();
    }
    if (isset($mapped_args['id'])) {
        $id = $handler::mappedArgs()['id'];
    }
    if (! isset($username) && isset($id)) {
        $handler->issueError(404);

        return;
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    $userHandle = isset($id) ? 'id' : 'username';
    $user = $personal_mode_user
        ?? User::getSingle(${$userHandle}, $userHandle);
    $is_owner = false;
    if (isset($user['id'], $logged_user['id'])) {
        $is_owner = $user['id'] === $logged_user['id'];
    }
    if (! $user
        || ($user['status'] ?? '') !== 'valid'
        && ($logged_user === [] || ! $handler::cond('content_manager'))) {
        $handler->issueError(404);

        return;
    }
    if (! $is_owner && ! $handler::cond('content_manager') && (bool) $user['is_private']) {
        $handler->issueError(404);

        return;
    }
    if (! (bool) env()['CHEVERETO_ENABLE_USERS'] && $user['id'] !== getSetting('website_mode_personal_uid')) {
        $handler->issueError(404);

        return;
    }
    if (getSetting('website_mode') === 'personal'
        && getSetting('website_mode_personal_routing') === '/'
    ) {
        if (str_starts_with($currentUrl, '/' . $user['username'])) {
            $redirectTo = str_replace_first('/' . $user['username'], '', $currentUrl);
            headersNoCache();
            redirect($redirectTo, 302);
        }
    }
    $pre_doctitle = '';
    $user_routes = [];
    $userHome = $user['home'] === 'files'
        ? 'files'
        : $user['home'];
    $user_views = [
        'files' => [
            'title' => _s(
                '%t by %s',
                [
                    '%t' => _n('File', 'Files', 20),
                ]
            ),
            'title_short' => _s('Files'),
        ],
        'albums' => [
            'title' => _s('%t by %s', [
                '%t' => _n('Album', 'Albums', 20),
            ]),
            'title_short' => _s('Albums'),
        ],
        'search' => [
            'title' => _s('Search'),
            'title_short' => _s('Search'),
        ],
    ];
    foreach (array_keys($user_views) as $k) {
        $user_routes[] = $k === $userHome
            ? $username
            : $k;
    }
    // albums: images, admin, search
    // images: admin, albums, search
    if (getSetting('enable_likes')) {
        $user_views['liked'] = [
            'title' => _s('Liked by %s'),
            'title_short' => _s('Liked'),
        ];
        $user_routes[] = 'liked';
    }
    if (getSetting('enable_followers')) {
        $user_views['following'] = [
            'title' => _s('%t followed by %s', [
                '%t' => _n('User', 'Users', 20),
            ]),
            'title_short' => _s('Following'),
        ];
        $user_views['followers'] = [
            'title' => _s('%t following %s', [
                '%t' => _n('User', 'Users', 20),
            ]),
            'title_short' => _s('Followers'),
        ];
        $user_routes[] = 'following';
        $user_routes[] = 'followers';
    }
    foreach (array_keys($user_views) as $k) {
        $user_views[$k]['current'] = false;
    }
    if (isset($request_handle[1])) {
        if ($request_handle[1] === 'search') {
            if (! $handler::cond('search_enabled')) {
                $handler->issueError(404);

                return;
            }
            if (! (request()['q'] ?? false)) {
                redirect($user['url'], 302);
            }
            $user['search'] = [
                'type' => empty(request()['list']) ? 'images' : request()['list'],
                'q' => request()['q'],
                'd' => strlen(request()['q']) >= 25 ? (substr(request()['q'], 0, 22) . '...') : request()['q'],
            ];
        }
        if ($request_handle[1] !== server()['QUERY_STRING']
            && ! in_array($request_handle[1], $user_routes, true)
        ) {
            $handler->issueError(404);

            return;
        }
        if ($request_handle[1] === 'search') {
            if (! server()['QUERY_STRING']) {
                $handler->issueError(404);

                return;
            }
            if (! empty(request()['list'])
                && ! in_array(request()['list'], ['images', 'albums', 'users'], true)
            ) {
                $handler->issueError(404);

                return;
            }
        }
        if (array_key_exists($request_handle[1], $user_views)) {
            $user_views[$request_handle[1]]['current'] = true;
        }
    } else {
        $user_views[$userHome]['current'] = true;
    }
    $user['followed'] = false;
    $show_follow_button = false;
    if (getSetting('website_mode') !== 'personal') {
        $user['followed'] = false;
        $show_follow_button = false;
        if ($logged_user !== []) {
            $user['followed'] = ($user['id'] === $logged_user['id'])
                ? false
                : Follow::doesFollow(
                    (int) $logged_user['id'],
                    (int) $user['id']
                );
            $show_follow_button = $user['id'] !== $logged_user['id']
                && $logged_user['is_private'] == 0;
        }
    }
    $handler::setCond('show_follow_button', $show_follow_button);
    $base_user_url = $user['url'];
    $type = $userHome;
    $current_view = $type;
    $tools = false;
    foreach ($user_views as $k => $v) {
        $handler::setCond('user_' . $k, (bool) $v['current']);
        if ($v['current']) {
            $current_view = $k;
            if ($current_view !== $userHome) {
                $base_user_url .= "/{$k}";
            }
        }
    }
    $currentKey = 0;
    $safe_html_user = safe_html($user);
    $sub_tabs = [];
    if ($current_view === 'liked') {
        $type = (get()['list'] ?? '') === 'albums'
            ? 'albums'
            : 'images';
        $sub_tabs = [
            [
                'icon' => 'fas fa-photo-film',
                'label' => _s('Files'),
                'url' => $base_user_url . '/?list=images',
                'current' => $type === 'images',
            ],
            [
                'icon' => 'fas fa-images',
                'label' => _s('Albums'),
                'url' => $base_user_url . '/?list=albums',
                'current' => $type === 'albums',
            ],
        ];
    }
    if ($type === 'files') {
        $type = 'images';
    }
    switch ($current_view) {
        case 'files':
        case 'liked':
            $tools = $is_owner || $handler::cond('content_manager');
            if ($current_view === 'liked') {
                $tools_available = $handler::cond('content_manager')
                    ? ['delete', 'category', 'flag']
                    : ['embed'];
            }

            break;
        case 'following':
        case 'followers':
            $type = 'users';
            $tools = false;
            $params_hidden = [
                $current_view . '_user_id' => $user['id_encoded'],
            ];
            $params_remove_keys = ['list'];

            break;
        case 'albums':
            $icon = 'fas fa-images';
            $type = 'albums';
            $tools = true;

            break;
        case 'search':
            $icon = 'fas fa-search';
            $type = $user['search']['type'];
            $currentKey = (isset(request()['list']) && request()['list'] === 'images') || ! isset(request()['list'])
                ? 0 : 1;
            $tabs = [
                [
                    'icon' => 'fas fa-image',
                    'type' => 'images',
                    'label' => _s('Images'),
                    'id' => 'list-user-images',
                    'current' => $currentKey === 0,
                ],
                [
                    'icon' => 'fas fa-images',
                    'type' => 'albums',
                    'label' => _n('Album', 'Albums', 20),
                    'id' => 'list-user-albums',
                    'current' => $currentKey === 1,
                ],
            ];
            foreach ($tabs as $k => $v) {
                $params = [
                    'list' => $v['type'],
                    'q' => $safe_html_user['search']['q'],
                    'sort' => 'date_desc',
                    'page' => '1',
                ];
                $tabs[$k]['params'] = http_build_query($params);
                $tabs[$k]['url'] = $base_user_url . '/?' . $tabs[$k]['params'];
            }

            break;
    }
    $icon = [
        'files' => 'fas fa-photo-film',
        'albums' => 'fas fa-images',
        'liked' => 'fas fa-heart',
        'following' => 'fas fa-rss',
        'followers' => 'fas fa-users',
        'search' => 'fas fa-search',
    ][$current_view];
    if ($user_views['albums']['current']) {
        $params_hidden['list'] = 'albums';
    }
    $params_hidden[$current_view === 'liked' ? 'like_user_id' : 'userid'] = $user['id_encoded'];
    $params_hidden['from'] = 'user';
    $tags_id = [];
    $tags_active = [];
    $isDisplayUsedTags = in_array($current_view, ['files', 'albums']);
    if (in_array($type, ['images', 'albums'])
        && $isDisplayUsedTags
        && (get()['tag'] ?? '') !== ''
    ) {
        $tags = Tag::get(get()['tag']);
        if ($tags !== []) {
            $tags_active = array_column($tags, 'name');
            $tags_id = array_column($tags, 'id');
            $tags_id_encoded = array_map(function ($id): string {
                return encodeID($id);
            }, $tags_id);
            $params_hidden['tag_id'] = implode(',', $tags_id_encoded);
        }
    }
    $tag_string = implode(', ', $tags_active);
    $tag_string_no_spaces = implode(',', $tags_active);
    $tagsTable = DB::getTable('tags');
    $tagsUsersTable = DB::getTable('tags_users');
    $tagsAlbumsTable = DB::getTable('tags_albums');
    $userId = (int) $user['id'];
    $tags_display = [];
    if ($isDisplayUsedTags) {
        $tagsSql = <<<MySQL
        SELECT t.tag_name name, tu.tag_user_count count
        FROM `{$tagsTable}` t
        INNER JOIN `{$tagsUsersTable}` tu
            ON tu.tag_user_tag_id=t.tag_id
            AND tu.tag_user_user_id={$userId}
            AND tu.tag_user_count > 0
        ORDER BY `tag_user_count` DESC, `tag_name` ASC
        LIMIT 20;

        MySQL;
        if ($type === 'albums') {
            $tagsSql = <<<MySQL
            SELECT t.tag_name name, ta.tag_album_count count
            FROM `{$tagsTable}` t
            INNER JOIN `{$tagsAlbumsTable}` ta
                ON ta.tag_album_tag_id=t.tag_id
                AND ta.tag_album_user_id={$userId}
                AND ta.tag_album_count > 0
            ORDER BY `tag_album_count` DESC, `tag_name` ASC
            LIMIT 20;

            MySQL;
        }
        $tags_display = DB::queryFetchAll($tagsSql);
        foreach ($tags_display as &$tag) {
            $base_tag_filter_url = $base_user_url . '/?tag=';
            $tag = Tag::row($tag['name'], $base_tag_filter_url . '%s');
            Tag::addUrlFilters($tag, $base_user_url, $tags_active);
        }
    }
    if (! isset($tabs)) {
        $tabs = Listing::getTabs([
            'listing' => $type,
            'basename' => $base_user_url,
            'tools' => $tools,
            'tools_available' => $tools_available ?? [],
            'params_hidden' => $params_hidden,
            'params_remove_keys' => $params_remove_keys ?? null,
            'tag' => rawurldecode($tag_string_no_spaces),
        ], [], true);
        $currentKey = $tabs['currentKey'];
        $tabs = $tabs['tabs'];
    }
    foreach ($tabs as $k => &$v) {
        if (! array_key_exists('params_hidden', $tabs)) {
            $tabs[$k]['params_hidden'] = http_build_query($params_hidden);
        }
        $v['disabled'] = $user[($user_views['files']['current'] ? 'image' : 'album') . '_count'] === 0 ? ! $v['current'] : false;
    }
    $listing = new Listing();
    if ($user['image_count'] > 0
        || $user['album_count'] > 0
        || in_array($current_view, ['liked', 'following', 'followers'], true)) {
        $getParams = Listing::getParams(request());
        Listing::fillCurrentTabPeekSeek($tabs, $currentKey, $getParams);
        $handler::setVar('list_params', $getParams);
        if ($getParams['sort'][0] === 'likes' && ! getSetting('enable_likes')) {
            $handler->issueError(404);

            return;
        }
        $tpl = $type;
        switch ($current_view) {
            case 'liked':
                $where = 'WHERE like_user_id=:user_id';
                $tpl = 'liked/' . match ($type) {
                    'images' => 'image',
                    'albums' => 'album',
                };

                break;
            case 'following':
                $where = 'WHERE follow_user_id=:user_id';

                break;
            case 'followers':
                $where = 'WHERE follow_followed_user_id=:user_id';

                break;
            default:
                $where = $type === 'images'
                    ? 'WHERE image_user_id=:user_id'
                    : 'WHERE album_user_id=:user_id';
                // if ($type === 'albums' && $tags_active === []) {
                //     $where .= ' AND album_parent_id IS NULL';
                // }

                break;
        }
        $output_tpl = 'user/' . $tpl;
        if ($user_views['search']['current']) {
            $type = $user['search']['type'];
            $where = $user['search']['type'] === 'images' ? 'WHERE image_user_id=:user_id AND MATCH(image_name, image_title, image_description, image_original_filename) AGAINST (:q)' : 'WHERE album_user_id=:user_id AND MATCH(album_name, album_description) AGAINST (:q)';
        }
        $show_user_items_editor = Login::isLoggedUser();
        if ($type === 'albums') {
            $show_user_items_editor = false;
        }

        try {
            $listing = new Listing();
            $listing->setType($type); // images | users | albums
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
            $listing->setWhere($where);
            $listing->setOwner((int) $user['id']);
            $listing->setTagsIds(...$tags_id);
            $listing->setTagsString($tag_string_no_spaces);
            $listing->setRequester(Login::getUser());
            if ($is_owner || $handler::cond('content_manager')) {
                if ($type === 'users') {
                    $listing->setTools(false);
                    $show_user_items_editor = false;
                } elseif ($current_view === 'liked') {
                    $listing->setTools(
                        $user['id'] === $logged_user['id']
                            ? ['embed']
                            : false
                    );
                } else {
                    $listing->setTools(true);
                }
            }
            $listing->bind(':user_id', $user['id']);
            if ($user_views['search']['current'] && ! empty($user['search']['q'])) {
                $handler::setVar('meta_robots', 'noindex, follow');
                $listing->bind(':q', $user['search']['q']);
            }
            $listing->setOutputTpl($output_tpl);
            $listing->exec();
        } catch (Exception) {
        } // Silence to avoid wrong input queries
    }
    $title = sprintf($user_views[$current_view]['title'], $user['name_short_html']);
    $title_short = sprintf($user_views[$current_view]['title_short'], $user['firstname_html']);
    if ($safe_html_user['search']['d'] ?? false) {
        $title = _s('Search results for %s', '<em><b>' . $user['search']['d'] . '</b></em>');
        $pre_doctitle .= $user['search']['d'] . ' - ';
    }
    $pre_doctitle .= sprintf($user_views[$current_view]['title'], $user['name_html']);
    if (getSetting('website_mode') === 'community' || $user['id'] !== getSetting('website_mode_personal_uid')) {
        $pre_doctitle .= ' (' . $user['username'] . ')';
    }
    $handler::setVar('pre_doctitle', $pre_doctitle);
    $handler::setCond('owner', (bool) $is_owner);
    $handler::setCond('show_user_items_editor', $show_user_items_editor ?? false);
    $handler::setVar('user', $user);
    $handler::setVar('safe_html_user', $safe_html_user);
    $handler::setVar('title', $title);
    $handler::setVar('title_short', $title_short);
    $handler::setVar('tabs', $tabs);
    $handler::setVar('listing', $listing);
    $handler::setVar('icon', $icon);
    if ($user_views['albums']['current']) {
        $meta_description = _s('%n (%u) albums on %w');
    } elseif ($user['bio'] ?? false) {
        $meta_description = $safe_html_user['bio'];
    } else {
        $meta_description = _s('%n (%u) on %w');
    }
    $handler::setVar('meta_description', strtr($meta_description, [
        '%n' => $user['name'],
        '%u' => $user['username'],
        '%w' => getSetting('website_name'),
    ]));
    if ($handler::cond('content_manager') || $is_owner) {
        $handler::setVar('user_items_editor', [
            'user_albums' => User::getAlbums($user),
            'type' => $user_views['albums']['current'] ? 'albums' : 'images',
        ]);
    }
    $handler::setVar('share_links_array', get_share_links());
    $handler::setVar('tags_display', $tags_display);
    $handler::setVar('tags_active', $tags_active);
    if ($tag_string !== '') {
        $handler::setVar('meta_keywords', $tag_string);
    }
    $canonical = get_public_url($tabs[$currentKey]['url']);
    $handler::setVar('canonical', $canonical);
    $handler::setVar('sub_tabs', $sub_tabs);
};
