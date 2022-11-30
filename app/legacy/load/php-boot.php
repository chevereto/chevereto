<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(503);
    }
    echo 'This server is currently running PHP ' . PHP_VERSION . ' and Chevereto needs at least PHP 8.0.0 to run.' . PHP_EOL;
    die(255);
}

require_once __DIR__ . '/loader.php';
