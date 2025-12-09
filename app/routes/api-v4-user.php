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
        '/api/4/albums',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/albums/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/albums/{idEncoded}/cover',
        // POST: ,
        // DELETE: ,
    ),
    route(
        '/api/4/files',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/files/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/files-flags',
        // POST: ,
    ),
    route(
        '/api/4/users',
        // GET: ,
    ),
    route(
        '/api/4/users/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/users/{idEncoded}/follow',
        // POST: ,
        // DELETE: ,
    ),
    route(
        '/api/4/users/{idEncoded}/like',
        // POST: ,
        // DELETE: ,
    ),
    route(
        '/api/4/tags',
        // POST: ,
        // GET: ,
    ),
    route(
        '/api/4/tags/{idEncoded}',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/account/notifications',
        // GET: ,
    ),
    route(
        '/api/4/account/avatar',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/account/background',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
    route(
        '/api/4/account/two-factor',
        // GET: ,
        // PATCH: ,
        // DELETE: ,
    ),
);
