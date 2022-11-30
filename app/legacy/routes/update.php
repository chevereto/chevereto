<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Vars\env;

return function (Handler $handler) {
    if (!(bool) env()['CHEVERETO_ENABLE_UPDATE_HTTP']
        || Settings::get('chevereto_version_installed') === null
    ) {
        $handler->issueError(404);

        return;
    }
    if (!Login::isAdmin()) {
        $handler->issueError(403);

        return;
    }
    require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
};
