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
        if (!isset($values['ip'])) {
            $values['ip'] = get_client_ip();
        }
        $values['date'] = datetime();
        $values['date_gmt'] = datetimegmt();

        return DB::insert('requests', $values);
    }

    public static function getCounts(array|string $type, string $result, ?string $ip = null): array
    {
        if (is_array($type)) {
            $type_qry = 'request_type IN(';
            $binds = [];
            foreach ($type as $i => $singleType) {
                $type_qry .= ':rt' . $i . ',';
                $binds[':rt' . $i] = $singleType;
            }
            $type_qry = rtrim($type_qry, ',') . ')';
        } else {
            $type_qry = 'request_type=:request_type';
            $binds = [
                ':request_type' => $type
            ];
        }

        $db = DB::getInstance();
        $db->query('SELECT
                        COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MINUTE), 1, NULL)) AS minute,
                        COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR), 1, NULL)) AS hour,
                        COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY), 1, NULL)) AS day,
                        COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 WEEK), 1, NULL)) AS week,
                        COUNT(IF(request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH), 1, NULL)) AS month
                    FROM ' . DB::getTable('requests') . ' WHERE ' . $type_qry . ' AND request_result=:request_result AND request_ip=:request_ip AND request_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH)');
        foreach ($binds as $k => $v) {
            $db->bind($k, $v);
        }
        $db->bind(':request_result', $result);
        $db->bind(':request_ip', $ip ?: get_client_ip());

        return $db->fetchSingle();
    }

    public static function delete($values, $clause = 'AND'): int
    {
        return DB::delete('requests', $values, $clause);
    }
}
