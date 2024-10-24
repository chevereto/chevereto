<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('CHEVERETO_PHP_VERSION_REQUIRED', '8.1.28');

if (version_compare(PHP_VERSION, CHEVERETO_PHP_VERSION_REQUIRED, '<')) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(503);
    }
    echo 'This server is running PHP '
        . PHP_VERSION
        . ' and Chevereto needs at least PHP '
        . CHEVERETO_PHP_VERSION_REQUIRED
        . PHP_EOL;
    exit(255);
}

require_once __DIR__ . '/loader.php';
