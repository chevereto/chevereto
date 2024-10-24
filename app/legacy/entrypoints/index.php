<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\G\sanitize_path_slashes;
use function Chevereto\Legacy\loaderHandler;

define('ACCESS', 'web');

$appDir = __DIR__ . '/../..';
$loadDir = __DIR__ . '/../load';
require_once $loadDir . '/php-boot.php';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$urlPath = parse_url($requestUri, PHP_URL_PATH);
if (str_ends_with($scriptName, 'index.php')) {
    $relative_root = sanitize_path_slashes(
        dirname($scriptName)
        . '/'
    );
    $urlPath = preg_replace('#' . $relative_root . '#', '/', $requestUri, 1);
}
if (in_array($urlPath, ['/upgrading', '/upgrading/'], true)
    && file_exists($appDir . '/.upgrading/upgrading.lock')) {
    require $appDir . '/upgrading.php';
    exit;
}
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
