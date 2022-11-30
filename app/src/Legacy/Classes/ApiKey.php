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

use function Chevere\Message\message;
use function Chevere\String\randomString;
use Chevere\Throwable\Exceptions\OutOfRangeException;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\datetimegmt;
use Throwable;

class ApiKey
{
    public static function generate(int $id): string
    {
        return 'chv_' . encodeID($id) . '_' . randomString(128);
    }

    public static function hash(string $key): string
    {
        return password_hash($key, PASSWORD_BCRYPT);
    }

    public static function verify(string $key): array
    {
        $explode = explode('_', $key);
        $idEncoded = $explode[1] ?? null;
        if ($idEncoded === null) {
            return [];
        }
        $id = decodeID($idEncoded);
        $get = self::get($id);
        if ($get === []) {
            return [];
        }
        $verify = password_verify($key, $get['hash']);
        if ($verify === false) {
            return [];
        }

        return [
            'id' => $get['id'],
            'user_id' => $get['user_id'],
            'date_gmt' => $get['date_gmt'],
        ];
    }

    public static function insert(int $userId): string
    {
        $values = [
            'user_id' => $userId,
            'date_gmt' => datetimegmt(),
            'hash' => '',
        ];
        $insert = DB::insert('api_keys', $values);
        $key = self::generate($insert);
        $hash = self::hash($key);
        DB::update('api_keys', ['hash' => $hash], ['id' => $insert]);

        return $key;
    }

    public static function remove(int $id): void
    {
        DB::delete('api_keys', ['id' => $id]);
    }

    public static function has(int $userId): bool
    {
        return self::getUserKey($userId) !== [];
    }

    public static function getUserPublic(int $userId): array
    {
        $get = self::getUserKey($userId);
        if ($get === []) {
            throw new OutOfRangeException(
                message('The user does not have an API key')
            );
        }

        return [
            'public' => 'chv_' . encodeID($get['id']) . '_***',
            'date_gmt' => $get['date_gmt'],
        ];
    }

    public static function get(int $id): array
    {
        try {
            $get = DB::get('api_keys', ['id' => $id], 'AND', ['field' => 'id', 'order' => 'desc'])[0] ?? null;
        } catch (Throwable) {
            return [];
        }

        return DB::formatRow($get, 'api_key') ?? [];
    }

    public static function getUserKey(int $userId): array
    {
        try {
            $get = DB::get('api_keys', ['user_id' => $userId], 'AND', ['field' => 'id', 'order' => 'desc'])[0] ?? null;
        } catch (Throwable) {
            return [];
        }

        return DB::formatRow($get, 'api_key') ?? [];
    }
}
