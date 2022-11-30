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

use Chevereto\Encryption\Interfaces\EncryptionInterface;

final class NullEncryption implements EncryptionInterface
{
    public function withNonce(string $nonce): self
    {
        return clone $this;
    }

    public function withRandomNonce(): self
    {
        return clone $this;
    }

    public function nonce(): string
    {
        return '';
    }

    public function encrypt(string $plainText): string
    {
        return $plainText;
    }

    public function decrypt(string $cipherText): string
    {
        return $cipherText;
    }
}
