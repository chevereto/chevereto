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

final class Cache
{
    private static KeyValueInterface $instance;

    public function __construct(KeyValueInterface $keyValue)
    {
        self::$instance = $keyValue;
    }

    public static function isEnabled(): bool
    {
        return ! (self::$instance instanceof KeyValueNull);
    }

    public static function instance(): KeyValueInterface
    {
        return self::$instance;
    }

    public static function hash(string $message): string
    {
        if (function_exists('sodium_crypto_generichash')) {
            return bin2hex(sodium_crypto_generichash($message, '', 20));
        }

        return sha1($message);
    }
}
