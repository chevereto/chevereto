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

/**
 * Describes the component in charge of encoding nonce and cipher text for database storage.
 */
interface EncodeInterface
{
    public function encrypt(string $text): string;
}
