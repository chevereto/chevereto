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
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\headersNoCache;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    if (version_compare(cheveretoVersionInstalled(), '3.7.0', '<')
        || ! getSetting('enable_followers')
    ) {
        $handler->issueError(404);

        return;
    }
    $logged_user = Login::getUser();
    if ($logged_user === []) {
        headersNoCache();
        redirect('login', 302);
    }
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    }
    $getParams = Listing::getParams(request());
    $tabs = Listing::getTabs([
        'listing' => 'images',
        'exclude_criterias' => ['most-oldest'],
        'params_hidden' => [
            'follow_user_id' => encodeID((int) $logged_user['id']),
        ],
    ], $getParams);
    $where = 'WHERE follow_user_id=:user_id';
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
    $listing->setRequester(Login::getUser());
    $listing->setWhere($where);
    $listing->bind(':user_id', $logged_user['id']);
    $listing->exec();
    $handler::setVar('pre_doctitle', _s('Following'));
    $handler::setVar('tabs', $tabs);
    $handler::setVar('listing', $listing);
    if (isset($logged_user['is_content_manager']) && $logged_user['is_content_manager']) {
        $handler::setVar('user_items_editor', false);
    }
    $handler::setVar('share_links_array', get_share_links());
};
