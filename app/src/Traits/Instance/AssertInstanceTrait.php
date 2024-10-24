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

use LogicException;
use function Chevere\Message\message;

trait AssertInstanceTrait
{
    public function assertInstance(string $property): void
    {
        if (! isset($this->${$property})) {
            throw new LogicException(
                message(
                    'Instance property `%property%` not initialized',
                    property: $property
                )
            );
        }
    }
}
