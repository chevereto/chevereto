<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Vars\Traits;

use Chevereto\Traits\Instance\AssertNoInstanceTrait;
use function DeepCopy\deep_copy;
use Ds\Map;

trait ImmutableMapTrait
{
    use AssertNoInstanceTrait;

    private static array $array = [];

    private static Map $map;

    public function __construct(array $array)
    {
        $this->assertNoInstance();
        static::$array = $array;
        static::$map = new Map($array);
    }

    public static function map(): Map
    {
        return deep_copy(static::$map);
    }

    public static function toArray(): array
    {
        return static::$array;
    }
}
