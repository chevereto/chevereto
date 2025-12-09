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

if (cheveretoVersionInstalled() !== '') {
    echo <<<PLAIN
    [ERROR] Chevereto is already installed

    PLAIN;
    exit(5);
}

require_once PATH_APP_LEGACY_INSTALL . 'installer.php';
