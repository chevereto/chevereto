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
use Chevereto\Encryption\Interfaces\KeyInterface;
use phpseclib3\Crypt\ChaCha20;

final class Encryption implements EncryptionInterface
{
    private ChaCha20 $cipher;

    private string $nonce;

    public function __construct(KeyInterface $key)
    {
        $this->nonce = randomNonce();
        $this->cipher = new ChaCha20();
        $this->cipher->setNonce($this->nonce);
        $this->cipher->setKey((string) $key);
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function withNonce(string $nonce): self
    {
        assertNonce($nonce);
        $new = clone $this;
        $new->nonce = $nonce;
        $new->cipher->setNonce($new->nonce);

        return $new;
    }

    public function withRandomNonce(): self
    {
        $new = clone $this;
        $new->nonce = randomNonce();
        $new->cipher->setNonce($new->nonce);

        return $new;
    }

    public function encrypt(string $plainText): string
    {
        return $this->cipher->encrypt($plainText);
    }

    public function decrypt(string $cipherText): string
    {
        return $this->cipher->decrypt($cipherText);
    }

    public function encryptEncode(string $plainText): string
    {
        return (new Encode($this->withRandomNonce()))
            ->binary($plainText);
    }

    public function encryptEncodeBase64(string $plainText): string
    {
        return (new Encode($this->withRandomNonce()))
            ->base64($plainText);
    }

    public function decodeDecrypt(string $nonceCipherText): string
    {
        $decode = new Decode($nonceCipherText);

        return $this
            ->withNonce($decode->nonce())
            ->decrypt($decode->cipherText());
    }
}
