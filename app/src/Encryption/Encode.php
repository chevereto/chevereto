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

    public function base64(string $plainText): string
    {
        return base64_encode(
            $this->binary($plainText)
        );
    }

    public function binary(string $plainText): string
    {
        return $this->encryption->nonce() . $this->encryption->encrypt($plainText);
    }
}
