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
use Chevereto\Legacy\Classes\Search;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\check_value;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Vars\env;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    if (!(bool) env()['CHEVERETO_ENABLE_USERS']) {
        $handler->issueError(403);

        return;
    }
    if ($handler::cond('search_enabled') == false) {
        $handler->issueError(404);

        return;
    }
    if (post() !== [] && !$handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    if ($handler->isRequestLevel(4)) {
        $handler->issueError(404);

        return;
    } // Allow only 3 levels
    if (is_null($handler->request()[0] ?? null)) {
        $handler->issueError(404);

        return;
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    if (!in_array($handler->request()[0], ['images', 'albums', 'users'])) {
        $handler->issueError(404);

        return;
    }
    $search = new Search();
    $search->q = request()['q'] ?? null;
    $search->type = $handler->request()[0];
    $search->request = request();
    $search->requester = Login::getUser();
    $search->build();
    if (!check_value($search->q)) {
        redirect();

        return;
    }
    $safe_html_search = safe_html($search->display);

    try {
        $getParams = Listing::getParams(request());
        $handler::setVar('list_params', $getParams);
        $listing = new Listing();
        $listing->setType($search->type);
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
        $listing->setWhere($search->wheres);
        $listing->setRequester(Login::getUser());
        foreach ($search->binds as $v) {
            $listing->bind($v['param'], $v['value']);
        }
        $listing->setOutputTpl($search->type);
        $listing->exec();
        $handler::setVar('listing', $listing);
    } catch (Exception $e) {
        $getParams = [];
    }
    $tabs = Listing::getTabs([
        'listing' => 'search',
        'basename' => 'search',
        'params' => ['q' => $safe_html_search['q'], 'page' => '1'],
        'params_remove_keys' => ['sort'],
    ], $getParams);
    foreach ($tabs as $k => &$v) {
        $v['current'] = $v['type'] == $search->type;
    }
    $meta_description = '';
    switch ($search->type) {
        case 'images':
            $meta_description = _s('Image search results for %s');

        break;
        case 'albums':
            $meta_description = _s('Album search results for %s');

        break;
        case 'users':
            $meta_description = _s('User search results for %s');

        break;
    }
    $handler::setVar('pre_doctitle', $search->q . ' - ' . _s('Search'));
    $handler::setVar('meta_description', sprintf($meta_description, $safe_html_search['q']));
    $handler::setVar('search', $search->display);
    $handler::setVar('safe_html_search', $safe_html_search);
    $handler::setVar('tabs', $tabs);
    if ($handler::cond('content_manager')) {
        $handler::setVar('user_items_editor', false);
    }
    $handler::setVar('share_links_array', get_share_links());
};
