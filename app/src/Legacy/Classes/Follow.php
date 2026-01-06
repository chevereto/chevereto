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

class Follow
{
    public static array $table_fields = [
        'date',
        'date_gmt',
        'user_id',
        'followed_user_id',
        'ip',
    ];

    public static function insert(array $args = []): int|array
    {
        return 0;
    }

    public static function delete(array|string $args = []): bool|array
    {
        return false;
    }

    public static function doesFollow(int $user_id, int $followed_id): bool
    {
        return false;
    }

    public static function getFollowersCount(array $args = []): array
    {
        return [];
    }

    public static function getSingle(array $args = []): array
    {
        return [];
    }

    public static function getAll(array $args = [], array $sort = []): array
    {
        return [];
    }

    public static function get(array $args, array $sort = [], ?int $limit = null): array
    {
        return [];
    }
}
