<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Stat;
use function Chevereto\Legacy\feedback;

$opts = getopt('C:') ?: [];
feedback('Rebuilding stats...');
Stat::rebuildTotals();
feedback('Stats rebuild process completed successfully');
exit(0);
