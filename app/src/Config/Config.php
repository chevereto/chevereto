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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use Chevereto\Traits\Instance\AssertStaticInstanceTrait;

final class Config
{
    use AssertStaticInstanceTrait;

    private static AssetConfig $asset;

    private static EnabledConfig $enabled;

    private static HostConfig $host;

    private static SystemConfig $system;

    private static LimitConfig $limit;

    public function __construct(
        AssetConfig $asset,
        EnabledConfig $enabled,
        HostConfig $host,
        SystemConfig $system,
        LimitConfig $limit,
    ) {
        if (isset(static::$asset)) {
            throw new LogicException(
                message('An instance of %type% has been already created.')
                    ->withCode('%type%', static::class),
                600
            );
        }
        static::$asset = $asset;
        static::$enabled = $enabled;
        static::$host = $host;
        static::$system = $system;
        static::$limit = $limit;
    }

    public static function asset(): AssetConfig
    {
        return static::$asset;
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
