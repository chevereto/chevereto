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

use Redis;

interface KeyValueInterface
{
    public function redis(): Redis;

    /**
     * Stores a value, regardless of whether or not the key already exists (in
     * which case it will overwrite the existing value for that key).
     *
     * Return value is a boolean true when the operation succeeds, or false on
     * failure.
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool;

    /**
     * Store multiple values at once.
     *
     * Return value will be an associative array in [key => status] form, where
     * status is a boolean true for success, or false for failure.
     *
     * setMulti is preferred over multiple individual set operations as you'll
     * set them all in 1 request.
     *
     * @param int     $expire Time when item falls out of the cache:
     *                        0 = permanent (doesn't expires);
     *                        under 2592000 (30 days) = relative time, in seconds from now;
     *                        over 2592000 = absolute time, unix timestamp
     *
     * @return bool[]
     */
    public function setMulti(array $values, int $expire = 0): array;

    /**
     * Retrieves an item from the cache.
     *
     * Optionally, an 2nd variable can be passed to this function. It will be
     * filled with a value that can be used for cas()
     *
     * @return mixed|bool Value, or false on failure
     */
    public function get(string $key): mixed;

    /**
     * Retrieves the cache key.
     */
    public function getKey(string $key): string;

    /**
     * Deletes an item from the cache.
     * Returns true if item existed & was successfully deleted, false otherwise.
     *
     * Return value is a boolean true when the operation succeeds, or false on
     * failure.
     */
    public function delete(string $key): bool;

    /**
     * Deletes multiple items at once (reduced network traffic compared to
     * individual operations).
     *
     * Return value will be an associative array in [key => status] form, where
     * status is a boolean true for success, or false for failure.
     *
     * @return bool[]
     */
    public function deleteMulti(string ...$key): array;
}
