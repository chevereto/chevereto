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

use BadMethodCallException;
use Redis;

class KeyValueNull implements KeyValueInterface
{
    public function __construct(
        private string $prefix = '',
    ) {
    }

    public function redis(): Redis
    {
        throw new BadMethodCallException('Redis is not available');
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return false;
    }

    public function setMulti(array $values, int $ttl = 0): array
    {
        return [];
    }

    public function get(string $key, &$token = null): mixed
    {
        return false;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function deleteMulti(string ...$key): array
    {
        return [];
    }

    public function getKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
