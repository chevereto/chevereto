<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Http\Controllers\Api\V4\TenantsConfigTraefikGet;
use Chevereto\Http\Middlewares\RestrictIpAccess;
use Chevereto\Http\Middlewares\TenantsApiKeyAuthorization;
use function Chevere\Router\route;
use function Chevere\Router\routes;

return routes(
    route(
        '/_/api/4/config/traefik',
        GET: TenantsConfigTraefikGet::class,
    ),
)
    ->withAppendMiddleware(
        RestrictIpAccess::with(
            allowList: '127.0.0.1,::1,172.16.0.0/12',
        ),
        TenantsApiKeyAuthorization::class,
    );
