<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    if (Login::isLoggedUser()) {
        Login::logout();
        $access_token = $handler::getAuthToken();
        $handler::setVar('auth_token', $access_token);
    }
};
