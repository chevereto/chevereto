<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Encryption;

use Chevereto\Encryption\Interfaces\EncodeInterface;
use Chevereto\Encryption\Interfaces\EncryptionInterface;

final class Encode implements EncodeInterface
{
    public function __construct(
        private EncryptionInterface $encryption
    ) {
    }

    public function encrypt(string $text): string
    {
        return base64_encode(
            $this->encryption->nonce() . $this->encryption->encrypt($text)
        );
    }
}
