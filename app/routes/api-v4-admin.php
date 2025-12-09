<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\Router\route;
use function Chevere\Router\routes;

return routes(
    route(
        '/api/4/storages',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/storages/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/storages/{idEncoded}/regenerate-stats',
        // POST: ,
    ),
    route(
        '/api/4/storages/{idEncoded}/migrate',
        // POST: ,
    ),
    route(
        '/api/4/categories',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/categories/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/ip-address-bans',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/ip-address-bans/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/tools/rebuild-stats',
        // GET: ,
    ),
    route(
        '/api/4/tools/test-email',
        // POST: ,
    ),
    route(
        '/api/4/id/{id}/encode',
        // GET: ,
    ),
    route(
        '/api/4/id/{idEncoded}/decode',
        // GET: ,
    ),
);
