<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Config;

use Chevereto\Traits\Instance\AssertStaticInstanceTrait;
use LogicException;
use function Chevere\Message\message;

final class Config
{
    use AssertStaticInstanceTrait;

    private static EnabledConfig $enabled;

    private static HostConfig $host;

    private static SystemConfig $system;

    private static LimitConfig $limit;

    public function __construct(
        EnabledConfig $enabled,
        HostConfig $host,
        SystemConfig $system,
        LimitConfig $limit,
    ) {
        if (isset(static::$asset)) {
            throw new LogicException(
                message(
                    'An instance of `%type%` has been already created.',
                    type: static::class
                ),
                600
            );
        }
        static::$enabled = $enabled;
        static::$host = $host;
        static::$system = $system;
        static::$limit = $limit;
    }

    public static function enabled(): EnabledConfig
    {
        return static::$enabled;
    }

    public static function host(): HostConfig
    {
        return static::$host;
    }

    public static function system(): SystemConfig
    {
        return static::$system;
    }

    public static function limit(): LimitConfig
    {
        return static::$limit;
    }
}
