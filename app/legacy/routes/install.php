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
use function Chevereto\Vars\env;

return function (Handler $handler) {
    $isService = env()['CHEVERETO_ENABLE_TENANTS'] === '1'
        || env()['CHEVERETO_TENANT'] !== ''
        || env()['CHEVERETO_CONTEXT'] === 'saas';
    if (cheveretoVersionInstalled() !== '' || $isService) {
        $handler->issueError(404);

        return;
    }

    require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
};
