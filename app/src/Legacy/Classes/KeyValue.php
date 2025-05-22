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

use MatthiasMullie\Scrapbook\Adapters\Redis as RedisAdapter;
use MatthiasMullie\Scrapbook\KeyValueStore;
use MatthiasMullie\Scrapbook\Scale\StampedeProtector;
use Redis;

class KeyValue implements KeyValueInterface
{
    private KeyValueStore $keyValueStore;

    public function __construct(
        private Redis $redis,
        private string $prefix = '',
        private int $maxTtl = 0,
        int $stampedeSla = 0
    ) {
        $redisAdapter = new RedisAdapter($redis);
        $this->keyValueStore = $redisAdapter;
        if ($stampedeSla > 0) {
            $this->keyValueStore = new StampedeProtector($redisAdapter, $stampedeSla);
        }
    }

    public function redis(): Redis
    {
        return $this->redis;
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if ($ttl === 0) {
            $ttl = $this->maxTtl;
        }
        $ttl = min($ttl, $this->maxTtl);

        return $this->keyValueStore->set(
            $this->getKey($key),
            $value,
            $ttl
        );
    }

    public function setMulti(array $items, int $expire = 0): array
    {
        if ($expire === 0) {
            $expire = $this->maxTtl;
        }
        $expire = min($expire, $this->maxTtl);

        return $this->keyValueStore->setMulti(
            array_combine(
                $this->getKeys(array_keys($items)),
                array_values($items)
            ),
            $expire
        );
    }

    public function get(string $key, &$token = null): mixed
    {
        return $this->keyValueStore->get(
            $this->getKey($key),
            $token
        );
    }

    public function delete(string $key): bool
    {
        return $this->keyValueStore->delete(
            $this->getKey($key)
        );
    }

    public function deleteMulti(string ...$key): array
    {
        return $this->keyValueStore->deleteMulti(
            $this->getKeys($key)
        );
    }

    public function getKey(string $key): string
    {
        return $this->prefix . $key;
    }

    private function getKeys(array $keys): array
    {
        return array_map(
            fn ($key) => $this->getKey($key),
            $keys
        );
    }
}
