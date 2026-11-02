<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\checkUpdates;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\getVariable;

if (cheveretoVersionInstalled() === '') {
    echo "[!] Chevereto is not installed.\n";
    exit(255);
}
if (! checkUpdates()) {
    echo "[ERROR] Unable to check for updates\n";
    exit(255);
}
$current = APP_VERSION;
$latest = getVariable('update_check_notified_release')->nullString() ?? $current;
if (version_compare($latest, $current, '>')) {
    echo "[!] Update available: {$latest} (current {$current})\n";
    exit(0);
}
echo "[OK] Chevereto {$current} is up to date\n";
