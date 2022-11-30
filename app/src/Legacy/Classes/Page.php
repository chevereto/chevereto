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

class Page
{
    public static function getSingle(string $var, $by = 'url_key'): array
    {
        return [];
    }

    public static function getAll(array $args = [], array $sort = []): array
    {
        return [];
    }

    public static function get(array $values, array $sort = [], int $limit = null): array
    {
        return [];
    }

    public static function getPath(?string $var = null): string
    {
        return PATH_PUBLIC_CONTENT_PAGES . (is_string($var) ? $var : '');
    }

    public static function getFields(): array
    {
        return self::$table_fields;
    }

    public static function update(int $id, array $values): int
    {
        return 0;
    }

    public static function writePage(array $args = []): bool
    {
        return false;
    }

    public static function fill(array &$page): void
    {
    }

    public static function formatRowValues(mixed &$values, mixed $row = []): void
    {
    }

    public static function insert(array $values = []): int
    {
        return 0;
    }

    public static function delete(array|int $page): int
    {
        return 0;
    }
}
