<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Cache;
use Chevereto\Legacy\Classes\Categories;
use Chevereto\Legacy\Classes\Tags;

if (! Cache::isEnabled()) {
    echo "[ERROR] Cache is not enabled\n";
    exit(255);
}
$keyValue = Cache::instance();
$redis = $keyValue->redis();
$prefix = $keyValue->getKey('');
$topLevel = [
    'pages_visible',
    'settings',
    'variables',
    Categories::CACHE_KEY,
    Tags::CACHE_KEY,
];
$nested = [
    'ip',
    'l',
    'rl',
    'u',
];
foreach ($topLevel as $topKey) {
    deleteCache($redis, "{$prefix}{$topKey}");
}
foreach ($nested as $nestedKey) {
    iterateCache($redis, "{$prefix}{$nestedKey}:");
}
iterateCache($redis, $prefix);
function iterateCache(Redis $redis, string $prefix): void
{
    $iterator = null;
    while ($iterator !== 0) {
        $scan = $redis->scan($iterator, "{$prefix}*");
        foreach ($scan as $key) {
            if (str_starts_with($key, "{$prefix}SESSION")
                || str_ends_with($key, '.stampede')
            ) {
                continue;
            }
            deleteCache($redis, $key);
        }
    }
}
function deleteCache(Redis $redis, string $key): void
{
    $result = (bool) $redis->del($key);
    $status = 'DELETE';
    if ($result === false && ! $redis->get($key)) {
        $status = '   404';
    }
    echo "* {$status} > {$key}\n";
}
