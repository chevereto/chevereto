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

$version = cheveretoVersionInstalled();
if ($version === '') {
    echo 'Chevereto is not installed' . PHP_EOL;
    exit(255);
}
echo $version . PHP_EOL;
