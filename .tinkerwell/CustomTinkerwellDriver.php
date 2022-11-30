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

class CustomTinkerwellDriver extends TinkerwellDriver
{
    public function canBootstrap($projectPath)
    {
        return file_exists($projectPath . '/app/legacy/load/loader.php');
    }

    public function bootstrap($projectPath)
    {
        define('ACCESS', 'web');
        define('REPL', true);
        require $projectPath . '/app/legacy/load/loader.php';
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
    }

    public function contextMenu()
    {
        return [
            Label::create('Detected Chevereto v4'),
            OpenURL::create('Chevereto Docs', 'https://v4-docs.chevereto.com/'),
        ];
    }
}
