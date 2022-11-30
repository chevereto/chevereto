<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Import;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;

return function (Handler $handler) {
    if (!Login::isAdmin()) {
        $handler->issueError(403);

        return;
    }
    // Allow 3 levels only -> /importer-jobs/X/process
    if ($handler->isRequestLevel(4)) {
        $handler->issueError(404);

        return;
    }
    if (is_null($handler->request()[0] ?? null) || is_null($handler->request()[1] ?? null)) {
        $handler->issueError(404);

        return;
    }
    $filepath = Import::PATH_JOBS . sprintf('%1$s/%2$s.txt', $handler->request()[0], $handler->request()[1]);
    if (!file_exists($filepath)) {
        $handler->issueError(404);

        return;
    }
    if (!headers_sent()) {
        header('Content-Type: text/plain');
    }
    readfile($filepath);
    exit;
};
