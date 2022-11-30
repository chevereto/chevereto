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

class IpBan
{
    public static function getSingle(array $args = []): array
    {
        return [];
    }

    public static function getAll(): array
    {
        return [];
    }

    public static function delete(array $args = []): int
    {
        return 0;
    }

    public static function update(array $where = [], array $values = []): int
    {
        return 0;
    }

    public static function insert(array $args = []): int
    {
        return 0;
    }

    public static function fill(array &$ip_ban): void
    {
    }

    public static function validateIP(string $ip, bool $wildcards = true): bool
    {
        return true;
    }
}
