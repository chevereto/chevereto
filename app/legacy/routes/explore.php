<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\headersNoCache;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    $logged_user = Login::getUser();
    if (! $handler::cond('explore_enabled') && ! ($logged_user['is_content_manager'] ?? false)) {
        $handler->issueError(404);

        return;
    }
    $doing = $handler->request()[0] ?? null;
    $baseUrlRouteName = get_base_url(get_route_name());
    if (! isset($doing)
        && getSetting('homepage_style') == 'route_explore'
        && str_contains(get_current_url(), $baseUrlRouteName)
    ) {
        $redirect = str_replace_first(
            $baseUrlRouteName,
            get_base_url(),
            get_current_url()
        );
        headersNoCache();
        redirect($redirect, 302);
    }
    $explore_discovery = $handler::var('explore_discovery') + $handler::var('explore_content');
    if (isset($doing) && ! array_key_exists($doing, $explore_discovery)) {
        $handler->issueError(404);

        return;
    }
    if ($handler->isRequestLevel(3)) {
        $handler->issueError(404);

        return;
    } // Allow only 3 levels
    $basename = getSetting('homepage_style') == 'route_explore' && $handler->cond('mapped_route')
        ? ''
        : get_route_name();
    if ($doing) {
        $basename .= ($basename ? '/' : '') . $doing;
    }
    $list = isset($doing) ? $explore_discovery[$doing] : [
        'label' => _s('Explore'),
        'icon' => 'fas fa-compass',
    ];

    $list['list'] = $doing === null
        ? get_route_name()
        : $doing;
    $doing ??= 'images';
    $listingParams = [
        'listing' => $list['list'],
        'basename' => $basename,
        'params_hidden' => [
            'hide_empty' => 1,
            'hide_banned' => 1,
        ],
    ];
    if ($listingParams['listing'] === 'albums') {
        $listingParams['params_hidden']['album_min_image_count'] = getSetting('explore_albums_min_image_count');
    }
    if ($doing == 'animated') {
        $listingParams['params_hidden'] = array_merge($listingParams['params_hidden'], [
            'is_animated' => 1,
            'is_image' => 1,
            'is_video' => 0,
        ]);
    }
    if ($doing == 'videos') {
        $listingParams['params_hidden'] = array_merge($listingParams['params_hidden'], [
            'is_image' => 0,
            'is_video' => 1,
        ]);
    }
    if ($doing == 'images') {
        $listingParams['params_hidden'] = array_merge($listingParams['params_hidden'], [
            'is_image' => 1,
            'is_video' => 0,
        ]);
    }
    $request = request();
    $getParams = Listing::getParams(
        request: $request,
        type: $request['list'] ?? $doing
    );
    $tabs = Listing::getTabs($listingParams, $getParams, true);
    $currentKey = $tabs['currentKey'];
    $type = $tabs['tabs'][$currentKey]['type'];
    $tabs = $tabs['tabs'];
    parse_str($tabs[$currentKey]['params'], $tab_params);
    $fix_sort = explode('_', $tab_params['sort']);
    if (count($fix_sort) == 2) {
        $getParams['sort'] = $fix_sort;
    }
    $handler::setVar('list_params', $getParams);
    $listing = new Listing();
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
    $listing->setParamsHidden($listingParams['params_hidden']);
    $listing->exec();
    $handler::setVar('list', $list);
    $handler::setVar('listing', $listing);
    if (getSetting('homepage_style') == 'route_explore') {
        $handler::setVar('doctitle', Settings::get('website_doctitle'));
        $handler::setVar('pre_doctitle', Settings::get('website_name'));
    } else {
        $handler::setVar('pre_doctitle', _s('Explore') . ' ' . $list['label']);
    }
    $handler::setVar('category', null);
    $handler::setVar('tabs', $tabs);
    if (isset($logged_user['is_content_manager']) && $logged_user['is_content_manager']) {
        $handler::setVar('user_items_editor', false);
    }
    $handler::setVar('share_links_array', get_share_links());
    $canonical = get_public_url($tabs[$currentKey]['url']);
    $handler::setVar('canonical', $canonical);
};
