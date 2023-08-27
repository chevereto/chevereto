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

class Notification
{
    public static array $content_types = ['image', 'album', 'like', 'follow'];

    public static function get(array $args = []): array
    {
        return [];
    }

    public static function insert(array $args = []): void
    {
    }

    public static function delete(array $args = []): void
    {
    }

    public static function markAsRead(array $args = []): void
    {
    }

    protected static function fill(array &$row): void
    {
    }

    protected static function hasContent(array $row): bool
    {
        return false;
    }
}
