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
 * Describes the component in charge of defining the encoded string stored in database.
 */
interface DecodeInterface extends Stringable
{
    public const ENCODING = '8bit';

    public function nonce(): string;

    public function cipherText(): string;
}
