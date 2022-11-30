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

interface EncryptionInterface
{
    public const NONCE_LENGTH = 12;

    public const KEY_LENGTH = 32;

    public function withNonce(string $nonce): self;

    public function withRandomNonce(): self;

    public function nonce(): string;

    public function encrypt(string $plainText): string;

    public function decrypt(string $cipherText): string;
}
