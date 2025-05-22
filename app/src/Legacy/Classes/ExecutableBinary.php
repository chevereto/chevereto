<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Chevereto\Legacy\Classes\Traits\BinaryTrait;

final class ExecutableBinary
{
    use BinaryTrait;

    public function name(): string
    {
        return $this->name;
    }
}
