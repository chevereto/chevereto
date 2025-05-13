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
use function Chevereto\Legacy\G\format_bytes;

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
foreach ($topLevel as $topKey) {
    $cacheKey = $prefix . $topKey;
    deleteCache($redis, $cacheKey);
}
$nested = [
    'ip',
    'l',
    'rl',
    'u',
];
foreach ($nested as $nestedKey) {
    $iterator = null;
    while ($iterator !== 0) {
        $scanKeys = $keyValue->redis()->scan($iterator, "{$prefix}{$nestedKey}:*");
        foreach ($scanKeys as $scanKey) {
            deleteCache($redis, $scanKey);
        }
    }
}
function deleteCache(Redis $redis, string $key): void
{
    $exists = $redis->exists($key);
    if (! $exists) {
        printf(
            "| %-60s | %-12s | %-8s |\n",
            $key,
            '--',
            '--'
        );

        return;
    }
    $ttl = $redis->ttl($key);
    if ($ttl === -1) {
        $ttlStr = 'no-expire';
    } elseif ($ttl === -2) {
        $ttlStr = 'expired';
    } else {
        $ttlStr = $ttl;
    }
    $bytes = $redis->rawCommand('MEMORY', 'USAGE', $key);
    $sizeReadable = format_bytes($bytes);
    printf(
        "| %-60s | %-12s | %-8s |\n",
        $key,
        $ttlStr,
        $sizeReadable
    );
}
