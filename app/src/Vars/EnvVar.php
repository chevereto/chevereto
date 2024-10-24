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
use Ds\Map;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\string;

final class EnvVar
{
    use ImmutableMapTrait;

    public const PREFIX = 'CHEVERETO_';

    public const REGEX_KEY = '/^' . self::PREFIX . '[A-Z0-9_]+$/';

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
        iterable(
            V: string(),
            K: string(self::REGEX_KEY)
        )($array);
        $this->assertNoInstance();
        static::$array = $array;
        static::$map = new Map($array);
    }
}
