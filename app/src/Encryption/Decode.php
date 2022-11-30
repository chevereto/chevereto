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

use Chevereto\Encryption\Interfaces\DecodeInterface;
use Chevereto\Encryption\Interfaces\EncryptionInterface;

final class Decode implements DecodeInterface
{
    private string $decoded;

    private string $nonce;

    private string $cipherText;

    public function __construct(string $encoded)
    {
        $this->decoded = base64_decode($encoded, true);
        $this->nonce = mb_substr(
            $this->decoded,
            0,
            EncryptionInterface::NONCE_LENGTH,
            self::ENCODING
        );
        assertNonce($this->nonce);
        $this->cipherText = mb_substr(
            $this->decoded,
            EncryptionInterface::NONCE_LENGTH,
            null,
            self::ENCODING
        );
    }

    public function __toString(): string
    {
        return $this->decoded;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function cipherText(): string
    {
        return $this->cipherText;
    }
}
