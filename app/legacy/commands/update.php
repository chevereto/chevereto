<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\cheveretoVersionInstalled;

if (cheveretoVersionInstalled() === '') {
    echo "[ERROR] Chevereto is not installed, try with the install command.\n";
    exit(255);
}
require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
