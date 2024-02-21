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

if (PHP_SAPI !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    die("403 Forbidden\n");
}
$opts = getopt('C:') ?: [];
if ($opts === []) {
    echo "Missing -C command\n";
    die(255);
} else {
    $access = $opts['C'];
    $options = [
        'cron',
        'update',
        'encrypt-secrets',
        'decrypt-secrets',
        'htaccess-checksum',
        'htaccess-enforce',
        'bulk-importer',
        'install',
        'langs',
        'password-reset',
        'setting-get',
        'setting-update',
        'version'
    ];
    if (!in_array($access, $options)) {
        echo "Invalid command\n";
        die(255);
    }
}
define('ACCESS', $access);
require_once __DIR__ . '/../load/php-boot.php';
require_once __DIR__ . '/../load/loader.php';
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
