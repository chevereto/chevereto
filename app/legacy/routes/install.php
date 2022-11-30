<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;

return function (Handler $handler) {
    if (Settings::get('chevereto_version_installed') !== null) {
        $handler->issueError(404);

        return;
    }
    require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
};
