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

use Chevere\VarSupport\StorableVariable;
use LogicException;
use PDOException;
use Throwable;
use function Chevere\Message\message;
use function Chevere\Parameter\getType;
use function Chevereto\Legacy\getSetting;

class Variable
{
    public const LEGACY_VARIABLES = [
        'chevereto_news' => 'array',
        'chevereto_version_installed' => 'string',
        'cron_last_ran' => 'string',
        'crypt_salt' => 'string',
        'id_padding' => 'int',
        'last_used_storage' => 'int',
        'news_check_datetimegmt' => 'string',
        'update_check_datetimegmt' => 'string',
        'update_check_notified_release' => 'string',
    ];

    public const KNOWN_VARIABLES = self::LEGACY_VARIABLES
        + [
            'storages_all' => 'int',
            'storages_active' => 'int',
            'login_providers_active' => 'int',
        ];

    protected static ?self $instance;

    protected static array $variables = [];

    public function __construct()
    {
        try {
            $rows = DB::get(
                table: 'variables',
                where: 'all',
                sort: [
                    'field' => 'name',
                    'order' => 'asc',
                ]
            );
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') {
                $rows = [];
                foreach (array_keys(static::KNOWN_VARIABLES) as $name) {
                    $failOverRow = static::failoverRow($name);
                    if ($failOverRow === []) {
                        continue;
                    }
                    $rows[] = $failOverRow;
                }
            } else {
                throw $e;
            }
        }
        foreach ($rows as &$row) {
            $row = DB::formatRow($row);
            static::populate(
                name: $row['name'],
                value: $row['value'],
                type: $row['type'],
            );
        }
        static::$instance = $this;
    }

    public static function getAll(): array
    {
        if (! isset(static::$instance)) {
            new static();
        }

        return static::$variables;
    }

    /**
     * @return int Number of affected rows, 0: no changes, 1: for updated/inserted
     */
    public static function set(string $name, mixed $value): int
    {
        $type = getType($value);
        $value = static::getValueAsString($type, $value);
        $db = DB::getInstance();
        $table = DB::getTable('variables');
        $prefix = DB::getFieldPrefix('variables');
        $sql = <<<SQL
        INSERT INTO `{$table}` ({$prefix}_name, {$prefix}_value, {$prefix}_type)
        VALUES (:name, :value, :type)
        ON DUPLICATE KEY UPDATE {$prefix}_value = :value, {$prefix}_type = :type;
        SQL;

        try {
            $db->query($sql);
            $db->bind(':name', $name);
            $db->bind(':value', $value);
            $db->bind(':type', $type);
            $return = $db->exec()
                ? min(1, $db->rowCount())
                : 0;
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') {
                $return = (int) Settings::update(
                    [
                        $name => $value,
                    ]
                );
            } else {
                throw $e;
            }
        }
        if ($return > 0) {
            static::populate(
                name: $name,
                value: $value,
                type: $type,
            );
        }

        return $return;
    }

    public static function get(string $name): mixed
    {
        if (! isset(static::$instance)) {
            new static();
        } elseif (! array_key_exists($name, static::$variables)) {
            static::fetch($name);
        }

        return static::$variables[$name] ?? null;
    }

    public static function fetch(string $name): mixed
    {
        $fetch = static::fetchRow($name);
        if ($fetch === false) {
            return null;
        }

        return static::getTyped($fetch['value'], $fetch['type']);
    }

    public static function delete(string $name): int
    {
        $return = DB::delete(
            table: 'variables',
            values: [
                'name' => $name,
            ]
        );
        unset(static::$variables[$name]);

        return $return;
    }

    public static function getTyped(mixed $value, ?string $type = null): mixed
    {
        $type ??= getType($type);
        $return = match ($type) {
            'string' => (string) $value,
            'bool' => ((string) $value) === '1',
            'int' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
        if (in_array($type, ['array', 'object'])) {
            try {
                $return = unserialize($value);
            } catch (Throwable) {
                $return = [];
                if ($type === 'object') {
                    $return = (object) $return;
                }
            }
        }

        return $return;
    }

    public static function getValueAsString(string $type, mixed $value): string
    {
        if (in_array($type, ['array', 'object'])) {
            // $value = (new StorableVariable($value))->toSerialize();
            $value = serialize($value);
        }

        return (string) $value;
    }

    protected static function insert(string $name, mixed $value, string $type): int
    {
        $value = static::getValueAsString($type, $value);
        $return = DB::insert(
            table: 'variables',
            values: [
                'name' => $name,
                'value' => $value,
                'type' => $type,
            ]
        ) ?: 0;
        if ($return > 0) {
            static::populate(
                name: $name,
                value: $value,
                type: $type,
            );
        }

        return $return;
    }

    protected static function populate(string $name, mixed $value, string $type): void
    {
        if (array_key_exists($name, static::KNOWN_VARIABLES)
            && $value !== null
            && $type !== static::KNOWN_VARIABLES[$name]
        ) {
            throw new LogicException(
                (string) message('Variable type mismatch for %name%', name: $name)
            );
        }
        static::$variables[$name] = isset($value)
            ? static::getTyped($value, $type)
            : null;
    }

    /**
     * @returns array|bool if the row exists, FALSE otherwise
     */
    protected static function fetchRow(string $name): array|false
    {
        try {
            $return = DB::get(
                table: 'variables',
                where: [
                    'name' => $name,
                ],
                limit: 1
            );
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') {
                $return = static::failoverRow($name);
                if ($return === []) {
                    $return = false;
                }
            } else {
                throw $e;
            }
        }
        if ($return === false) {
            static::populate(
                name: $name,
                value: null,
                type: 'string',
            );

            return false;
        }
        $return = DB::formatRow($return);
        static::populate(
            name: $name,
            value: $return['value'],
            type: $return['type'],
        );

        return $return;
    }

    private static function failoverRow(string $name): array
    {
        if (! array_key_exists($name, static::KNOWN_VARIABLES)) {
            return [];
        }
        $value = array_key_exists($name, static::LEGACY_VARIABLES)
            ? (getSetting($name) ?? '')
            : null;

        return [
            'name' => $name,
            'value' => $value,
            'type' => static::KNOWN_VARIABLES[$name],
        ];
    }
}
