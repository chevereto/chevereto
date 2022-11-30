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
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;

return routes(
    route(
        path: '/api/1/upload/',
        POST: new UploadPostController()
    ),
);
