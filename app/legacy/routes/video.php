<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\virtualRouteHandleRedirect;

return function (Handler $handler) {
    $route = 'image';
    virtualRouteHandleRedirect($route, $handler->requestArray()[0], 'video');
    $handler->mapRoute($route);
    $routeCallable = include PATH_APP_LEGACY_ROUTES . $route . '.php';

    return $routeCallable($handler);
};
