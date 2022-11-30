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

use Chevereto\Legacy\G\DB as GDB;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Vars\env;
use PDO;

class DB extends GDB
{
    public const TABLES = [
        'albums',
        'api_keys',
        'categories',
        'confirmations',
        'deletions',
        'follows',
        'images_hash',
        'images',
        'import',
        'importing',
        'ip_bans',
        'likes',
        'logins',
        'login_connections',
        'login_cookies',
        'login_passwords',
        'login_providers',
        'notifications',
        'pages',
        'queue',
        'requests',
        'settings',
        'stats',
        'storage_apis',
        'storages',
        'two_factors',
        'users',
    ];

    public const PREFIX_TO_TABLE = [
        'category' => 'categories',
        'deleted' => 'deletions',
        'image_hash' => 'images_hash',
    ];

    public const TABLES_TO_PREFIX = [
        'categories' => 'category',
        'deletions' => 'deleted',
        'images_hash' => 'image_hash',
    ];

    public static function getTable(string $table): string
    {
        return env()['CHEVERETO_DB_TABLE_PREFIX'] . $table;
    }

    public static function getTables(): array
    {
        $return = [];
        foreach (self::TABLES as $table) {
            $return[$table] = self::getTable($table);
        }

        return $return;
    }

    public static function get(
        array|string $table,
        array|string $values,
        string $clause = 'AND',
        array $sort = [],
        int $limit = null,
        int $fetch_style = PDO::FETCH_ASSOC
    ): mixed {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        $sort = self::getPrefixedSort($prefix, $sort);

        return GDB::get($table, $values, $clause, $sort, $limit, $fetch_style);
    }

    public static function update(
        string $table,
        array $values,
        array $wheres,
        string $clause = 'AND'
    ): int {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        $wheres = self::getPrefixedValues($prefix, $wheres);

        return GDB::update($table, $values, $wheres, $clause);
    }

    public static function insert($table, $values): int
    {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);

        return GDB::insert($table, $values);
    }

    public static function increment(
        string $table,
        array $values,
        array $wheres,
        string $clause = 'AND'
    ): int|false {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        $wheres = self::getPrefixedValues($prefix, $wheres);

        return GDB::increment($table, $values, $wheres, $clause);
    }

    public static function delete(
        string $table,
        array $values,
        string $clause = 'AND'
    ): int {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);

        return GDB::delete($table, $values, $clause);
    }

    public static function formatRow(mixed $row, string $prefix = ''): mixed
    {
        if (!is_array($row)) {
            return $row;
        }
        if ($prefix == '') {
            $array = $row;
            reset($array);
            preg_match('/^([a-z0-9]+)_{1}/', (string) key($array), $match);
            $prefix = $match[1] ?? '';
        }
        $output = [];
        foreach ($row as $k => $v) {
            $k = (string) $k;
            if (!starts_with($prefix, $k)) {
                $new_key = preg_match('/^([a-z0-9]+)_/i', (string) $k, $new_key_match);
                $new_key = $new_key_match[1] ?? null;
                if ($new_key === null) {
                    continue;
                }
                $output[$new_key][str_replace($new_key . '_', '', $k)] = $v;
                unset($output[$k]);
            } else {
                $output[str_replace($prefix . '_', '', $k)] = $v;
            }
        }

        return $output;
    }

    public static function formatRows($get, string $prefix = '')
    {
        if (isset($get[0]) && is_array($get[0])) {
            foreach ($get as $k => $v) {
                self::formatRowValues($get[$k], $v, $prefix);
            }
        } elseif (!empty($get)) {
            self::formatRowValues(values: $get, prefix: $prefix);
        }

        return $get;
    }

    public static function formatRowValues(array|string &$values, array|string $row = [], string $prefix = ''): void
    {
        $values = self::formatRow($row !== [] ? $row : $values, $prefix);
    }

    public static function getTableFromFieldPrefix(string $prefix, bool $db_table_prefix = true): string
    {
        $table = array_key_exists($prefix, self::PREFIX_TO_TABLE)
            ? self::PREFIX_TO_TABLE[$prefix]
            : $prefix . 's';

        return $db_table_prefix ? self::getTable($table) : $table;
    }

    public static function getFieldPrefix(array|string $table): string
    {
        if (is_array($table)) {
            $array = $table;
            $table = $array['table'];
        }
        if (array_key_exists($table, self::TABLES_TO_PREFIX)) {
            return self::TABLES_TO_PREFIX[$table];
        } else {
            return rtrim($table, 's');
        }
    }

    protected static function getPrefixedValues(string $prefix, array|string $values): array|string
    {
        if (!is_array($values)) {
            return $values;
        }
        $values_prefix = [];
        if (is_array($values)) {
            foreach ($values as $k => $v) {
                $values_prefix[$prefix . '_' . $k] = $v;
            }
        }

        return $values_prefix;
    }

    protected static function getPrefixedSort(string $prefix, array $sort): array
    {
        if ($sort !== [] && !empty($sort['field'])) {
            $sort['field'] = $prefix . '_' . $sort['field'];
        }

        return $sort;
    }
}
