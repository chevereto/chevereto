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

trait ImmutableMapTrait
{
    use AssertNoInstanceTrait;

    private static array $array = [];

    public function __construct(array $array)
    {
        $this->assertNoInstance();
        static::$array = $array;
    }

    public static function toArray(): array
    {
        return static::$array;
    }
}
