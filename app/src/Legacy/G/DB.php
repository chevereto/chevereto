<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\G;

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use function Chevereto\Vars\env;
use Exception;
use PDO;
use PDOStatement;

class DB
{
    private array $pdo_default_attrs = [];

    private static ?self $instance;

    private array $pdo_options = [];

    public static PDO $dbh;

    public PDOStatement $query;

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
        $this->pdo_options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET time_zone = '+00:00', NAMES 'utf8mb4'";
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
        if (!isset(self::$instance)) {
            throw new LogicException(
                message('No %type% initialized')
                    ->withCode('%type%', static::class)
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

    public function bind(mixed $param, mixed $value, int $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;

                break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;

                break;
                case is_null($value):
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
        $this->exec();

        return $this->query->fetchAll($mode);
    }

    public function fetchSingle(int $mode = PDO::FETCH_ASSOC): mixed
    {
        $this->exec();

        return $this->query->fetch($mode);
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

        return $limit == 1
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

    public function endTransaction()
    {
        return self::$dbh->commit();
    }

    public function cancelTransaction()
    {
        return self::$dbh->rollBack();
    }

    public static function getTable(string $table)
    {
        return env()['CHEVERETO_DB_TABLE_PREFIX'] . $table;
    }

    public static function get(
        array|string $table,
        array|string $values,
        string $clause = 'AND',
        array $sort = [],
        int $limit = null,
        int $fetch_style = PDO::FETCH_ASSOC
    ): mixed {
        if (!is_array($values) && $values !== 'all') {
            throw new Exception('Expecting array values, ' . gettype($values) . ' given');
        }
        self::validateClause($clause, __METHOD__);
        if (is_array($table)) {
            $join = $table['join'];
            $table = $table['table'];
        }
        $table = self::getTable($table);
        $query = 'SELECT * FROM ' . $table;
        if (isset($join)) {
            $query .= ' ' . $join . ' ';
        }
        if (is_array($values) && !empty($values)) {
            $query .= ' WHERE ';
            foreach ($values as $k => $v) {
                if (is_null($v)) {
                    $query .= '`' . $k . '` IS :' . $k . ' ' . $clause . ' ';
                } else {
                    $query .= '`' . $k . '`=:' . $k . ' ' . $clause . ' ';
                }
            }
        }
        $query = rtrim($query, $clause . ' ');
        if (!empty($sort)) {
            if (!$sort['field']) {
                $sort['field'] = 'date';
            }
            if (!$sort['order']) {
                $sort['order'] = 'desc';
            }
            $query .= ' ORDER BY ' . $sort['field'] . ' ' . strtoupper($sort['order']) . ' ';
        }
        if ($limit && is_int($limit)) {
            $query .= " LIMIT $limit";
        }
        $db = self::getInstance();
        $db->query($query);
        if (is_array($values)) {
            foreach ($values as $k => $v) {
                $db->bind(':' . $k, $v);
            }
        }
        $fetch_style = (int) $fetch_style;

        return $limit == 1
            ? $db->fetchSingle($fetch_style)
            : $db->fetchAll($fetch_style);
    }

    public static function update(
        string $table,
        array $values,
        array $wheres,
        string $clause = 'AND'
    ): int {
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

        return $db->exec() ? $db->rowCount() : false;
    }

    public static function insert(string $table, array $values): int|false
    {
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
        $table = self::getTable($table);
        $query = 'UPDATE `' . $table . '` SET ';
        foreach ($values as $k => $v) {
            if (preg_match('/^([\+\-]{1})\s*([\d]+)$/', (string) $v, $matches)) { // 1-> op 2-> number
                $query .= '`' . $k . '`=';
                if ($matches[1] == '+') {
                    $query .= '`' . $k . '`' . $matches[1] . $matches[2] . ',';
                }
                if ($matches[1] == '-') {
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

        return $db->exec() ? $db->rowCount() : false;
    }

    public static function delete(
        string $table,
        array $values,
        string $clause = 'AND'
    ): int {
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
            '%table_prefix%' => env()['CHEVERETO_DB_TABLE_PREFIX']
        ]);
    }

    public static function dbPrepare(string $query, array $values): DB
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

        return $fetch === false
            ? []
            : $fetch;
    }

    public static function fetchAllQuery(string $query, array $binds, int $mode = PDO::FETCH_ASSOC): array
    {
        $db = self::dbPrepare($query, $binds);

        return $db->exec() ? $db->fetchAll($mode) : [];
    }

    private static function validateClause(string $clause, string|null $method = null)
    {
        $clause = strtoupper($clause);
        if (!in_array($clause, ['AND', 'OR', ''])) {
            throw new Exception('Expecting clause string \'AND\' or \'OR\' in ' . ($method ?? __CLASS__));
        }
    }
}
