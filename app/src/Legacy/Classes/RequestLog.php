<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Vars\env;

class RequestLog
{
    public static function get($values, $sort = [], $limit = null): array
    {
        return DB::get('requests', $values, 'AND', $sort, $limit);
    }

    public static function insert(array $values): int
    {
        if (defined('PHPUNIT_CHEVERETO_TESTSUITE')) {
            return 0;
        }
        if (! isset($values['ip'])) {
            $values['ip'] = get_client_ip();
        }
        $values['date'] = datetime();
        $values['date_gmt'] = datetimegmt();
        $rows = DB::insert('requests', $values);
        if ($rows && Cache::isEnabled()) {
            $cache = Cache::instance();
            $redis = $cache->redis();
            $ip = inet_ntop(inet_pton($values['ip']));
            $key = Cache::instance()->getKey("ip:{$ip}:rl");
            $set = $redis->sMembers($key) ?: [];
            foreach ($set as $hash) {
                $cacheKeyLog = $cache->getKey("rl:{$hash}");
                $redis->del($cacheKeyLog);
                $redis->sRem($key, $hash);
            }
        }

        return $rows;
    }

    public static function getCounts(array|string $type, string $result, ?string $ip = null): array
    {
        $ip ??= get_client_ip();
        if (Cache::isEnabled()) {
            $hash = Cache::hash(serialize($type) . $result . $ip);
            $cacheKey = "rl:{$hash}";
            $cached = Cache::instance()->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        if (is_array($type)) {
            $whereType = 'request_type IN(';
            $binds = [];
            foreach ($type as $i => $singleType) {
                $whereType .= ':rt' . $i . ',';
                $binds[':rt' . $i] = $singleType;
            }
            $whereType = rtrim($whereType, ',') . ')';
        } else {
            $whereType = 'request_type=:request_type';
            $binds = [
                ':request_type' => $type,
            ];
        }
        $binds[':request_result'] = $result;
        $binds[':request_ip'] = $ip;
        $db = DB::getInstance();
        $tableRequest = DB::getTable('requests');
        $sql = <<<SQL
        SELECT
            COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MINUTE), 1, NULL)) AS minute,
            COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR), 1, NULL)) AS hour,
            COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY), 1, NULL)) AS day,
            COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 WEEK), 1, NULL)) AS week,
            COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH), 1, NULL)) AS month
        FROM `{$tableRequest}` WHERE request_result=:request_result
            AND {$whereType}
            AND request_ip=:request_ip
            AND request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH)
        SQL;
        $db->query($sql);
        foreach ($binds as $k => $v) {
            $db->bind($k, $v);
        }
        $row = $db->fetchSingle();
        $ttl = (int) (env()['CHEVERETO_CACHE_TIME_MICRO'] ?? 60);
        if (Cache::isEnabled()) {
            Cache::instance()->set($cacheKey, $row, $ttl);
            $redis = Cache::instance()->redis();
            $ip = inet_ntop(inet_pton($ip));
            $inverseKey = Cache::instance()->getKey("ip:{$ip}:rl");
            $redis->sAdd($inverseKey, $hash);
            $redis->expire($inverseKey, $ttl);
        }

        return $row;
    }

    public static function delete($values, $clause = 'AND'): int
    {
        return DB::delete('requests', $values, $clause);
    }
}
