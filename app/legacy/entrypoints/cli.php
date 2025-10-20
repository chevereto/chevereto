<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevere\ThrowableHandler\ThrowableHandler;
use function Chevereto\Legacy\getCheveretoEnv;
use function Chevereto\Legacy\loaderHandler;

if (PHP_SAPI !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    echo '403 Forbidden';
    exit(255);
}
$opts = getopt('C:') ?: [];
if ($opts === []) {
    echo 'Missing -C command' . PHP_EOL;
    exit(255);
}
$access = $opts['C'];
$options = [
    'bulk-importer',
    'cache-view',
    'cache-flush',
    'cron',
    'decrypt-secrets',
    'encrypt-secrets',
    'htaccess-checksum',
    'htaccess-enforce',
    'install',
    'js',
    'langs',
    'password-reset',
    'setting-get',
    'setting-update',
    'update',
    'version',
    'metrics',
];
if (! in_array($access, $options, true)) {
    echo 'Invalid command' . PHP_EOL;
    exit(255);
}
if (defined('APP_BIN_LEGACY')) {
    echo 'Note: This CLI is migrating to app/bin/cli' . PHP_EOL . PHP_EOL;
}
define('ACCESS', $access);
require_once __DIR__ . '/../load/php-boot.php';
set_error_handler(ThrowableHandler::ERROR_AS_EXCEPTION);
set_exception_handler(ThrowableHandler::CONSOLE);
require_once loaderHandler(
    getCheveretoEnv(),
    $_COOKIE,
    $_FILES,
    $_GET,
    $_POST,
    $_REQUEST,
    $_SERVER,
    $_SESSION ?? []
);
