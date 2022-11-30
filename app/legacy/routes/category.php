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
use function Chevereto\Legacy\G\get_route_name;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    if (!$handler::cond('explore_enabled')) {
        $handler->issueError(404);

        return;
    }
    $category = null;
    $categories = $handler::var('categories');
    $category_url_key = $handler->request()[0] ?? false;
    if (!$category_url_key) {
        redirect('explore');
    }
    if ($category_url_key) {
        foreach ($categories as $v) {
            if ($v['url_key'] == $category_url_key) {
                $category = $v;

                break;
            }
        }
        if (!$category) {
            $handler->issueError(404);

            return;
        }
        $handler::setVar('pre_doctitle', $category['name']);
    }
    $getParams = Listing::getParams(request());
    $tabs = Listing::getTabs([
        'listing' => 'images',
        'basename' => get_route_name() . '/' . $category['url_key'],
        'params_hidden' => ['category_id' => $category['id'], 'hide_banned' => 1],
    ], $getParams);
    $handler::setVar('list_params', $getParams);
    $listing = new Listing();
    $listing->setType('images');
    if (isset($getParams['reverse'])) {
        $listing->setReverse($getParams['reverse']);
    }
    if (isset($getParams['seek'])) {
        $listing->setSeek($getParams['seek']);
    }
    $listing->setOffset($getParams['offset']);
    $listing->setLimit($getParams['limit']); // how many results?
    $listing->setSortType($getParams['sort'][0]); // date | size | views
    $listing->setSortOrder($getParams['sort'][1]); // asc | desc
    $listing->setCategory($category['id']);
    $listing->setRequester(Login::getUser());
    $listing->exec();
    $meta_description = $category['description'] ?? '';
    $handler::setVar('meta_description', htmlspecialchars($meta_description));
    $handler::setVar('category', $category);
    $handler::setVar('tabs', $tabs);
    $handler::setVar('listing', $listing);
    $handler->setTemplate('explore');
    $handler::setVar('share_links_array', get_share_links());
};
