<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Vars;

use Chevereto\Vars\Traits\ImmutableMapTrait;

final class EnvVar
{
    use ImmutableMapTrait;

    public const PREFIX = 'CHEVERETO_';

    public const REGEX_KEY = '/^' . self::PREFIX . '[A-Z0-9_]+$/';

    public const PUTENV = [
        'CHEVERETO_ENVIRONMENT',
        'CHEVERETO_DEBUG_LEVEL',
    ];

    /**
     * @param array<string, string> $array
     */
    public function __construct(array $array)
    {
        foreach (array_keys($array) as $key) {
            if (str_starts_with($key, self::PREFIX)) {
                continue;
            }
            unset($array[$key]);
        }
        $this->assertNoInstance();
        foreach (self::PUTENV as $putenv) {
            if (array_key_exists($putenv, $array)) {
                putenv($putenv . '=' . $array[$putenv]);
            }
        }
        static::$array = $array;
    }
}
