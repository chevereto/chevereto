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

use Exception;
use LogicException;
use PDO;
use PDOStatement;
use function Chevere\Message\message;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Vars\env;

class DB
{
    public const TABLES = [
        'albums',
        'api_keys',
        'assets',
        'categories',
        'confirmations',
        'deletions',
        'follows',
        'images',
        'import',
        'importing',
        'ip_bans',
        'likes',
        'login_connections',
        'login_cookies',
        'login_passwords',
        'login_providers',
        'logins',
        'notifications',
        'pages',
        'queue',
        'requests',
        'settings',
        'stats',
        'storage_apis',
        'storages',
        'tags_albums',
        'tags_files',
        'tags_users',
        'tags',
        'two_factors',
        'users',
        'variables',
        'uploads',
        'uploads_chunks',
        'tenants',
        'tenants_plans',
        'tenants_stats',
        'tenants_api_keys',
        'tenants_variables',
    ];

    public const PREFIX_TO_TABLE = [
        'category' => 'categories',
        'deleted' => 'deletions',
        'tag_file' => 'tags_files',
        'tag_user' => 'tags_users',
        'tag_album' => 'tags_albums',
        'upload_chunk' => 'uploads_chunks',
    ];

    public const TABLES_TO_PREFIX = [
        'categories' => 'category',
        'deletions' => 'deleted',
        'tags_files' => 'tag_file',
        'tags_users' => 'tag_user',
        'tags_albums' => 'tag_album',
        'uploads_chunks' => 'upload_chunk',
        'tenants' => '',
        'tenants_plans' => '',
        'tenants_stats' => '',
        'tenants_api_keys' => '',
        'tenants_variables' => '',
    ];

    public static PDO $dbh;

    public PDOStatement $query;

    protected array $pdo_default_attrs = [];

    protected static ?object $instance;

    protected array $pdo_options = [];

    public function __construct(
        private string $host,
        private int $port,
        private string $name,
        private string $user,
        private string $pass,
        private string $driver,
        private array $pdoAttrs,
        private string $tablePrefix, // @phpstan-ignore-line
    ) {
        if (isset(self::$dbh)) {
            return;
        }
        $pdo_connect = $this->driver . ':host=' . $this->host . ';dbname=' . $this->name;
        if (isset($this->port)) {
            $pdo_connect .= ';port=' . $this->port;
        }
        $this->pdo_default_attrs = [
            PDO::ATTR_TIMEOUT => 30,
        ];
        $this->pdo_options = $this->pdo_default_attrs + $this->pdoAttrs;
        $this->pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $attrInitOption = class_exists('Pdo\\Mysql') ? \Pdo\Mysql::ATTR_INIT_COMMAND : PDO::MYSQL_ATTR_INIT_COMMAND;
        $this->pdo_options[] = "SET time_zone = '+00:00', NAMES 'utf8mb4'";
        $this->pdo_options[$attrInitOption] = "SET time_zone = '+00:00', NAMES 'utf8mb4'";
        self::$dbh = new PDO($pdo_connect, $this->user, $this->pass, $this->pdo_options);
        self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        self::$instance = $this;
    }

    public static function fromEnv()
    {
        new self(
            host: env()['CHEVERETO_DB_HOST'],
            port: (int) env()['CHEVERETO_DB_PORT'],
            name: env()['CHEVERETO_DB_NAME'],
            user: env()['CHEVERETO_DB_USER'],
            pass: env()['CHEVERETO_DB_PASS'],
            driver: env()['CHEVERETO_DB_DRIVER'],
            pdoAttrs: json_decode(
                env()['CHEVERETO_DB_PDO_ATTRS'],
                true
            ),
            tablePrefix: env()['CHEVERETO_DB_TABLE_PREFIX'],
        );
    }

    public static function hasInstance(): bool
    {
        return isset(self::$instance);
    }

    public static function getInstance(): self
    {
        if (! isset(self::$instance)) {
            throw new LogicException(
                message('No `%type%` initialized', type: static::class)
            );
        }

        return self::$instance;
    }

    public function setPDOAttrs(array $attributes): void
    {
        $this->pdo_options = $attributes;
    }

    public function setPDOAttr(string $key, string $value): void
    {
        $this->pdo_options[$key] = $value;
    }

    public function getAttr($attr): mixed
    {
        return self::$dbh->getAttribute($attr);
    }

    public function query(string $query): void
    {
        $this->query = self::$dbh->prepare($query);
    }

    public function errorInfo(): array
    {
        return self::$dbh->errorInfo();
    }

    public function bind(mixed $param, mixed $value, ?int $type = null): void
    {
        if ($type === null) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;

                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;

                    break;
                case $value === null:
                    $type = PDO::PARAM_NULL;

                    break;
                case is_resource($value):
                    $type = PDO::PARAM_LOB;

                    break;
                default:
                    $type = PDO::PARAM_STR;

                    break;
            }
        }
        $this->query->bindValue($param, $value, $type);
    }

    public function exec(): bool
    {
        return $this->query->execute();
    }

    public function fetchColumn(): mixed
    {
        return $this->query->fetchColumn();
    }

    public function closeCursor(): bool
    {
        return $this->query->closeCursor();
    }

    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array|false
    {
        return $this->exec() ?
            $this->query->fetchAll($mode)
            : false;
    }

    public function fetchSingle(int $mode = PDO::FETCH_ASSOC): mixed
    {
        return $this->exec()
            ? $this->query->fetch($mode)
            : false;
    }

    /**
     * @param string $query Raw query to execute.
     * @return int Number of rows affected.
     */
    public static function queryExecute(string $query): int
    {
        $db = self::getInstance();
        $db->query($query);

        return $db->exec() ? $db->rowCount() : 0;
    }

    /**
     * @param string $query Prepared query to execute.
     * @param array $binds Parameters to bind to the query `[:param => replace]`.
     * @return int Number of rows affected.
     */
    public static function preparedQueryExecute(string $query, array $binds): int
    {
        $db = self::dbPrepare($query, $binds);

        return $db->exec() ? $db->rowCount() : 0;
    }

    public static function queryFetchSingle(string $query, $fetch_style = null): array|false
    {
        return self::queryFetch($query, 1, $fetch_style);
    }

    public static function queryFetchAll(string $query, $fetch_style = null): array|false
    {
        return self::queryFetch($query, 0, $fetch_style);
    }

    public static function queryFetch(string $query, int $limit = 1, ?int $fetch_style = null): array|false
    {
        $db = self::getInstance();
        $db->query($query);
        if ($fetch_style === null) {
            $fetch_style = PDO::FETCH_ASSOC;
        }

        return $limit === 1
            ? $db->fetchSingle($fetch_style)
            : $db->fetchAll($fetch_style);
    }

    public function rowCount(): int
    {
        return $this->query->rowCount();
    }

    public function lastInsertId()
    {
        return self::$dbh->lastInsertId();
    }

    public function beginTransaction()
    {
        return self::$dbh->beginTransaction();
    }

    public function commitTransaction()
    {
        return self::$dbh->commit();
    }

    public function rollBackTransaction()
    {
        return self::$dbh->rollBack();
    }

    public static function getTable(string $table)
    {
        return env()['CHEVERETO_DB_TABLE_PREFIX'] . $table;
    }

    /**
     * On limit=1 returns FALSE if no results found.
     */
    public static function get(
        array|string $table,
        array|string $where,
        string $clause = 'AND',
        array $sort = [],
        ?int $limit = null,
        int $fetch_style = PDO::FETCH_ASSOC,
        array $valuesOperators = [],
        array $columns = [],
    ): mixed {
        $prefix = self::getFieldPrefix($table);
        $where = self::getPrefixedValues($prefix, $where);
        $valuesOperators = self::getPrefixedValues($prefix, $valuesOperators);
        $sort = self::getPrefixedSort($prefix, $sort);
        if (! is_array($where) && $where !== 'all') {
            throw new Exception('Expecting array values, ' . gettype($where) . ' given');
        }
        self::validateClause($clause, __METHOD__);
        if (is_array($table)) {
            $join = $table['join'];
            $table = $table['table'];
        }
        $table = self::getTable($table);
        $selectColumns = implode(', ', $columns);
        if (empty($selectColumns)) {
            $selectColumns = '*';
        }
        $query = <<<SQL
        SELECT {$selectColumns} FROM `{$table}`
        SQL;
        if (isset($join)) {
            $query .= ' ' . $join . ' ';
        }
        if (is_array($where) && ! empty($where)) {
            $query .= ' WHERE ';
            foreach ($where as $k => $v) {
                if ($v === null) {
                    $query .= '`' . $k . '` IS :' . $k . ' ' . $clause . ' ';
                } else {
                    $operator = $valuesOperators[$k] ?? '=';
                    $query .= '`' . $k . '`' . $operator . ':' . $k . ' ' . $clause . ' ';
                }
            }
        }
        $query = rtrim($query, $clause . ' ');
        if (! empty($sort)) {
            if (! $sort['field']) {
                $sort['field'] = 'date';
            }
            if (! $sort['order']) {
                $sort['order'] = 'desc';
            }
            $query .= ' ORDER BY ' . $sort['field'] . ' ' . strtoupper($sort['order']) . ' ';
        }
        if ($limit && is_int($limit)) {
            $query .= " LIMIT {$limit}";
        }
        $db = self::getInstance();
        $db->query($query);
        if (is_array($where)) {
            foreach ($where as $k => $v) {
                $db->bind(':' . $k, $v);
            }
        }
        $fetch_style = (int) $fetch_style;

        return $limit === 1
            ? $db->fetchSingle($fetch_style)
            : $db->fetchAll($fetch_style);
    }

    public static function update(
        string $table,
        array $values,
        array $wheres,
        string $clause = 'AND'
    ): int|false {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        $wheres = self::getPrefixedValues($prefix, $wheres);
        self::validateClause($clause, __METHOD__);
        $table = self::getTable($table);
        $query = 'UPDATE `' . $table . '` SET ';
        foreach (array_keys($values) as $k) {
            $query .= '`' . $k . '`=:value_' . $k . ',';
        }
        $query = rtrim($query, ',') . ' WHERE ';
        foreach (array_keys($wheres) as $k) {
            $query .= '`' . $k . '`=:where_' . $k . ' ' . $clause . ' ';
        }
        $query = rtrim($query, $clause . ' ');
        $db = self::getInstance();
        $db->query($query);
        foreach ($values as $k => $v) {
            $db->bind(':value_' . $k, $v);
        }
        foreach ($wheres as $k => $v) {
            $db->bind(':where_' . $k, $v);
        }

        return $db->exec()
            ? $db->rowCount()
            : false;
    }

    public static function insert(string $table, array $values): int|false
    {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        $table = self::getTable($table);
        $table_fields = [];
        $table_fields = array_keys($values);
        $query = 'INSERT INTO
					`' . $table . '` (`' . ltrim(implode('`,`', $table_fields), '`,`') . '`)
					VALUES (' . ':' . str_replace(':', ',:', implode(':', $table_fields)) . ')';
        $db = self::getInstance();
        $db->query($query);
        foreach ($values as $k => $v) {
            $db->bind(':' . $k, $v);
        }

        return $db->exec()
            ? (int) $db->lastInsertId()
            : false;
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
        $table = self::getTable($table);
        $query = 'UPDATE `' . $table . '` SET ';
        foreach ($values as $k => $v) {
            if (preg_match('/^([\+\-]{1})\s*([\d]+)$/', (string) $v, $matches)) { // 1-> op 2-> number
                $query .= '`' . $k . '`=';
                if ($matches[1] === '+') {
                    $query .= '`' . $k . '`' . $matches[1] . $matches[2] . ',';
                }
                if ($matches[1] === '-') {
                    $query .= 'GREATEST(cast(`' . $k . '` AS SIGNED) - ' . $matches[2] . ', 0),';
                }
            }
        }
        $query = rtrim($query, ',') . ' WHERE ';
        foreach (array_keys($wheres) as $k) {
            $query .= '`' . $k . '`=:where_' . $k . ' ' . $clause . ' ';
        }
        $query = rtrim($query, $clause . ' ');
        $db = self::getInstance();
        $db->query($query);
        foreach ($wheres as $k => $v) {
            $db->bind(':where_' . $k, $v);
        }

        return $db->exec()
            ? $db->rowCount()
            : false;
    }

    public static function delete(
        string $table,
        array $values,
        string $clause = 'AND'
    ): int {
        $prefix = self::getFieldPrefix($table);
        $values = self::getPrefixedValues($prefix, $values);
        self::validateClause($clause, __METHOD__);
        $table = self::getTable($table);
        $query = 'DELETE FROM `' . $table . '` WHERE ';
        foreach (array_keys($values) as $k) {
            $query .= '`' . $k . '`=:' . $k . ' ' . $clause . ' ';
        }
        $query = rtrim($query, $clause . ' ');
        $db = self::getInstance();
        $db->query($query);
        foreach ($values as $k => $v) {
            $db->bind(':' . $k, $v);
        }

        return $db->exec() ? $db->rowCount() : 0;
    }

    public static function getQueryWithTablePrefix(string $query): string
    {
        return strtr($query, [
            '%table_prefix%' => env()['CHEVERETO_DB_TABLE_PREFIX'],
        ]);
    }

    public static function dbPrepare(string $query, array $values = []): self
    {
        $query = self::getQueryWithTablePrefix($query);
        $db = self::getInstance();
        $db->query($query);
        foreach ($values as $key => $value) {
            $db->bind($key, $value);
        }

        return $db;
    }

    public static function fetchSingleQuery(string $query, array $binds, int $mode = PDO::FETCH_ASSOC): array
    {
        $db = self::dbPrepare($query, $binds);
        $fetch = $db->fetchSingle($mode);

        return $fetch ?: [];
    }

    public static function fetchAllQuery(string $query, array $binds, int $mode = PDO::FETCH_ASSOC): array
    {
        $db = self::dbPrepare($query, $binds);

        return $db->fetchAll($mode) ?: [];
    }

    /**
     * @param string $table Table name (no prefix).
     * @param string $constraint Foreign key name (no prefix).
     */
    public function getSqlDropForeignKey(string $table, string $constraint): string
    {
        $table = self::getTable($table);
        $constraint = self::getTable($constraint);
        $stmt = static::$dbh->prepare(
            <<<SQL
            SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY';
            SQL
        );
        $stmt->execute([$table, $constraint]);
        if ($stmt->fetchColumn() > 0) {
            return <<<SQL
            ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`;

            SQL;
        }

        return '';
    }

    /**
     * @param string $table Table name (no prefix).
     */
    public function getSqlDropIndex(string $table, string $index): string
    {
        $table = self::getTable($table);
        $pdo = static::$dbh;
        $stmt = $pdo->prepare(
            <<<SQL
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
            SQL
        );
        $stmt->execute([$table, $index]);
        if ($stmt->fetchColumn() > 0) {
            return <<<SQL
            DROP INDEX `{$index}` ON `{$table}`;

            SQL;
        }

        return '';
    }

    public static function getTables(): array
    {
        $return = [];
        foreach (self::TABLES as $table) {
            $return[$table] = self::getTable($table);
        }

        return $return;
    }

    public static function detectPrefix(string $column): string
    {
        foreach (self::PREFIX_TO_TABLE as $prefix => $table) {
            if (str_starts_with($column, $prefix . '_')) {
                return $prefix;
            }
        }
        preg_match('/^([a-z0-9]+)_{1}/', $column, $match);

        return $match[1] ?? '';
    }

    public static function formatRow(mixed $row, string $prefix = ''): mixed
    {
        if (! is_array($row)) {
            return $row;
        }
        if ($prefix == '') {
            $prefix = self::detectPrefix((string) array_key_first($row));
        }
        $output = [];
        foreach ($row as $k => $v) {
            $k = (string) $k;
            if (! starts_with($prefix, $k)) {
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
        } elseif (! empty($get)) {
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
        }

        return rtrim($table, 's');
    }

    public static function translate(string $query, string|int|float ...$pair)
    {
        $pair['table_prefix'] = env()['CHEVERETO_DB_TABLE_PREFIX'];

        return message($query, ...$pair)->__toString();
    }

    protected static function getPrefixedValues(string $prefix, array|string $values): array|string
    {
        if (! is_array($values) || $prefix === '') {
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
        if ($prefix === '') {
            return $sort;
        }
        if ($sort !== [] && ! empty($sort['field'])) {
            $sort['field'] = $prefix . '_' . $sort['field'];
        }

        return $sort;
    }

    private static function validateClause(string $clause, string|null $method = null)
    {
        $clause = strtoupper($clause);
        if (! in_array($clause, ['AND', 'OR', ''], true)) {
            throw new Exception('Expecting clause string \'AND\' or \'OR\' in ' . ($method ?? __CLASS__));
        }
    }
}
