<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\loaderHandler;

define('ACCESS', 'web');

require_once __DIR__ . '/../load/php-boot.php';
require_once loaderHandler(
    $_COOKIE,
    $_ENV,
    $_FILES,
    $_GET,
    $_POST,
    $_REQUEST,
    $_SERVER,
    $_SESSION ?? []
);
