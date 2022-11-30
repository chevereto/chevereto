<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Traits\Instance;

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;

trait AssertStaticInstanceTrait
{
    public static function assertInstance(
        string $property = 'instance',
        string $type = self::class
    ): void {
        if (!isset(static::${$property})) {
            throw new LogicException(
                message('Instance property %property% %type% not initialized.')
                    ->withCode('%type%', $type)
                    ->withCode('%property%', '$' . $property)
            );
        }
    }
}
