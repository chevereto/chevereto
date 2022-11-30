<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Encryption\Interfaces;

use Stringable;

/**
 * @method string __toString() The key as string
 */
interface KeyInterface extends Stringable
{
    public function base64(): string;
}
