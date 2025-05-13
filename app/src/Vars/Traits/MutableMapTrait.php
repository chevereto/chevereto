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

use Chevere\DataStructure\Interfaces\MapMutableInterface;
use Chevere\DataStructure\MapMutable;
use Chevereto\Traits\Instance\AssertNoInstanceTrait;
use Chevereto\Traits\Instance\AssertStaticInstanceTrait;

trait MutableMapTrait
{
    use AssertStaticInstanceTrait;

    use AssertNoInstanceTrait;

    private static MapMutableInterface $map;

    public function __construct(array $array)
    {
        $this->assertNoInstance();
        static::$map = new MapMutable(...$array);
    }

    public static function map(): MapMutableInterface
    {
        return static::$map;
    }

    public static function toArray(): array
    {
        return static::$map->toArray();
    }
}
