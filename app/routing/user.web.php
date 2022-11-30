<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function App\Controllers\legacyController;
use function Chevere\Router\route;
use function Chevere\Router\routes;

return routes(
    route(
        name: 'index',
        path: '/',
    ),
    route(
        name: 'account',
        path: '/account/',
        GET: legacyController('account.php'),
        POST: legacyController('account.php'),
    ),
    route(
        name: 'album',
        path: '/album/',
        GET: legacyController('album.php'),
        POST: legacyController('album.php'),
    ),
    route(
        name: 'category',
        path: '/category/',
        GET: legacyController('category.php'),
    ),
    route(
        name: 'connect',
        path: '/connect/',
        GET: legacyController('connect.php'),
    ),
    route(
        name: 'explore',
        path: '/explore/',
        GET: legacyController('explore.php'),
    ),
    route(
        name: 'following',
        path: '/following/',
        GET: legacyController('following.php'),
    ),
    route(
        name: 'image',
        path: '/image/',
        GET: legacyController('image.php'),
    ),
    route(
        name: 'login',
        path: '/login/',
        GET: legacyController('login.php'),
        POST: legacyController('login.php'),
    ),
    route(
        name: 'logout',
        path: '/logout/',
        GET: legacyController('logout.php'),
    ),
    route(
        name: 'moderate',
        path: '/moderate/',
        GET: legacyController('moderate.php'),
    ),
    route(
        name: 'oembed',
        path: '/oembed/',
        GET: legacyController('oembed.php'),
    ),
    route(
        name: 'page',
        path: '/page/',
        GET: legacyController('page.php'),
    ),
    route(
        name: 'plugin',
        path: '/plugin/',
        GET: legacyController('plugin.php'),
    ),
    route(
        name: 'captcha-verify',
        path: '/captcha-verify/',
        GET: legacyController('captcha-verify.php'),
    ),
    route(
        name: 'redirect',
        path: '/redirect/',
        GET: legacyController('redirect.php'),
    ),
    route(
        name: 'search',
        path: '/search/',
        GET: legacyController('search.php'),
        POST: legacyController('search.php'),
    ),
    route(
        name: 'settings',
        path: '/settings/',
        GET: legacyController('settings.php'),
        POST: legacyController('settings.php'),
    ),
    route(
        name: 'signup',
        path: '/signup/',
        GET: legacyController('signup.php'),
        POST: legacyController('signup.php'),
    ),
    route(
        name: 'upload',
        path: '/upload/',
        GET: legacyController('upload.php'),
    ),
    route(
        name: 'user',
        path: '/user/',
        GET: legacyController('user.php'),
    ),
);
