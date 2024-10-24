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
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Vars\env;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    if (! $handler::cond('content_manager')) {
        $handler->issueError(404);

        return;
    }
    if (! (bool) env()['CHEVERETO_ENABLE_MODERATION']) {
        $handler->issueError(403);

        return;
    }
    $list = [
        'label' => _s('Moderate'),
        'icon' => 'fas fa-check-double',
    ];
    $list['list'] = get_route_name();
    $listingParams = [
        'listing' => $list['list'],
        'basename' => get_route_name(),
        'params_hidden' => [
            'approved' => 0,
            'hide_empty' => 0,
            'hide_banned' => 0,
            'album_min_image_count' => 0,
        ],
        'exclude_criterias' => ['most-viewed', 'most-liked'],
        'order' => ['most-oldest', 'most-recent'],
    ];
    $getParams = Listing::getParams(request());
    $tabs = Listing::getTabs($listingParams, $getParams, true);
    $currentKey = $tabs['currentKey'];
    $type = $tabs['tabs'][$currentKey]['type'];
    $tabs = $tabs['tabs'];
    parse_str($tabs[$currentKey]['params'], $tabs_params);
    $getParams['sort'] = explode('_', $tabs_params['sort']); // Hack this stuff
    $handler::setVar('list_params', $getParams);
    $listing = new Listing();
    $listing->setApproved(0);
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
    $handler::setVar('pre_doctitle', _s('Moderate'));
    $handler::setVar('category', null);
    $handler::setVar('tabs', $tabs);
    $handler::setVar('share_links_array', get_share_links());
};
