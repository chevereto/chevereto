<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Stat;
use function Chevereto\Legacy\printTable;
use function Chevereto\Vars\env;

$opts = getopt('C:', ['format:']) ?: [];
$format = $opts['format'] ?? '';
$db = DB::getInstance();
$tablePrefix = env()['CHEVERETO_DB_TABLE_PREFIX'];
$db->query(
    Stat::getStatQuery($tablePrefix)
);
$stat = $db->fetchSingle();
if ($format === 'json') {
    echo json_encode($stat, JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    printTable($stat);
}
exit(0);
