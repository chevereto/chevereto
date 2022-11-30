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
use Chevereto\Traits\Instance\AssertStaticInstanceTrait;
use Ds\Map;

trait MutableMapTrait
{
    use AssertStaticInstanceTrait;

    use AssertNoInstanceTrait;

    private static Map $map;

    public function __construct(array $array)
    {
        $this->assertNoInstance();
        static::$map = new Map($array);
    }

    public static function map(): Map
    {
        return static::$map;
    }

    public static function toArray(): array
    {
        return static::$map->toArray();
    }
}
