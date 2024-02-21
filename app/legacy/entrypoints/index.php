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

$appDir = __DIR__ . '/../..';
$loadDir = __DIR__ . '/../load';
require_once $loadDir . '/php-boot.php';
$uri = $_SERVER['REQUEST_URI'] ?? '';
$parseUri = parse_url($uri);
if (in_array($parseUri['path'], ['/upgrading', '/upgrading/'])
    && file_exists($appDir . '/.upgrading/upgrading.lock')) {
    require $appDir . '/upgrading.php';
    exit;
}
require_once $loadDir . '/loader.php';
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
