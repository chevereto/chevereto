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
use function Chevereto\Legacy\getCounts;

$opts = getopt('C:f:') ?: [];
$format = $opts['f'] ?? '';
$totals = Stat::getTotals();
$table = DB::getTable('users');
$fetch = DB::queryFetch(
    <<<SQL
    SELECT
        SUM(CASE WHEN `user_is_admin` = 1 THEN 1 ELSE 0 END) AS admins,
        SUM(CASE WHEN `user_is_manager` = 1 THEN 1 ELSE 0 END) AS managers
    FROM {$table}
    SQL,
);
$counts = getCounts('pages', 'storages', 'categories');
$metrics = [
    'users' => $totals['users'],
    'admins' => $fetch['admins'],
    'managers' => $fetch['managers'],
    'pages' => $counts['pages'],
    'storages' => $counts['storages'],
    'files' => $totals['images'],
    'albums' => $totals['albums'],
    'tags' => $totals['tags'],
    'categories' => $counts['categories'],
    'storage_used' => $totals['disk_used'],
    'cron_time' => $totals['cron_time'],
    'file_views' => $totals['image_views'],
    'file_likes' => $totals['image_likes'],
    'album_views' => $totals['album_views'],
    'album_likes' => $totals['album_likes'],
];
if ($format === 'json') {
    echo json_encode($metrics, JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    foreach ($metrics as $key => $value) {
        echo "{$key}: {$value}\n";
    }
}
exit(0);
