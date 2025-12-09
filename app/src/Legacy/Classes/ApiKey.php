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

use InvalidArgumentException;
use OutOfRangeException;
use Throwable;
use function Chevere\Message\message;
use function Chevere\Standard\randomString;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\getVariable;
use function Chevereto\Legacy\passwordHash;

class ApiKey
{
    public static function generate(int $id): string
    {
        $random = randomString(64);
        $idEncoded = encodeID($id);
        $data = "{$idEncoded}_{$random}";
        $signature = hash_hmac(
            algo: 'sha256',
            data: $data,
            key: getVariable('hmac_secret_api_key')->string()
        );

        return 'chv_'
            . $data
            . '_'
            . $signature;
    }

    public static function hash(string $key): string
    {
        return passwordHash($key);
    }

    public static function verify(string $key): array
    {
        $explode = explode('_', $key);
        $count = count($explode);
        if (! in_array($count, [3, 4])) {
            return [];
        }
        $chv = $explode[0];
        if ($chv !== 'chv') {
            throw new InvalidArgumentException('Invalid API key prefix');
        }
        $idEncoded = $explode[1];
        $id = decodeID($idEncoded);
        $random = $explode[2];
        if ($count === 3) {
            if (version_compare(cheveretoVersionInstalled(), '4.4.0', '>=')) {
                throw new InvalidArgumentException(
                    'This API key format is no longer supported. Please generate a new API key.'
                );
            }
        } else {
            $signature = $explode[3];
            $generated = hash_hmac(
                'sha256',
                "{$idEncoded}_{$random}",
                getVariable('hmac_secret_api_key')->string()
            );
            if (! hash_equals($generated, $signature)) {
                return [];
            }
        }
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
        $insert = DB::insert(
            'api_keys',
            [
                'user_id' => $userId,
                'date_gmt' => datetimegmt(),
                'hash' => '',
            ]
        );
        $key = self::generate($insert);
        DB::update(
            'api_keys',
            [
                'hash' => self::hash($key),
            ],
            [
                'id' => $insert,
            ]
        );

        return $key;
    }

    public static function remove(int $id): void
    {
        DB::delete('api_keys', [
            'id' => $id,
        ]);
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
        $idEncoded = encodeID($get['id']);

        return [
            'public' => 'chv_' . $idEncoded . '_***',
            'date_gmt' => $get['date_gmt'],
        ];
    }

    public static function get(int $id): array
    {
        try {
            $get = DB::get(
                'api_keys',
                [
                    'id' => $id,
                ],
                'AND',
                [
                    'field' => 'id',
                    'order' => 'desc',
                ]
            )[0] ?? null;
        } catch (Throwable) {
            return [];
        }

        return DB::formatRow($get, 'api_key') ?? [];
    }

    public static function getUserKey(int $userId): array
    {
        try {
            $get = DB::get(
                'api_keys',
                [
                    'user_id' => $userId,
                ],
                'AND',
                [
                    'field' => 'id',
                    'order' => 'desc',
                ]
            )[0] ?? null;
        } catch (Throwable) {
            return [];
        }

        return DB::formatRow($get, 'api_key') ?? [];
    }
}
