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
define('REPL', true);
require __DIR__ . '/legacy/load/loader.php';
include loaderHandler(
    _cookie: [],
    _env: $_ENV,
    _files: [],
    _get: [],
    _post: [],
    _request: [],
    _server: [],
    _session: [
        'G_auth_token' => str_repeat('a', 40),
    ],
);

return [
    'startupMessage' =>
        <<<EOM
              __                        __
         ____/ /  ___ _  _____ _______ / /____
        / __/ _ \/ -_) |/ / -_) __/ -_) __/ _ \\
        \__/_//_/\__/|___/\__/_/  \__/\__/\___/

        💫 This is a REPL (Read-Eval-Print-Loop) environment.
        EOM,
    'updateCheck' => 'never',
    'runtimeDir' => __DIR__ . '/.psysh',
    'configDir' => __DIR__ . '/.psysh',
];
