<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\cheveretoVersionInstalled;

return function (Handler $handler) {
    if (cheveretoVersionInstalled() !== '') {
        $handler->issueError(404);

        return;
    }
    require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
};
