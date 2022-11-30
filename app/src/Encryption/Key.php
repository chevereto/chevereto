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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use Chevereto\Encryption\Interfaces\EncryptionInterface;
use Chevereto\Encryption\Interfaces\KeyInterface;
use function Safe\base64_decode;

final class Key implements KeyInterface
{
    private string $key;

    public function __construct(private string $base64)
    {
        $this->key = base64_decode($base64);
        if (strlen($this->key) !== EncryptionInterface::KEY_LENGTH) {
            throw new InvalidArgumentException(
                message('Requires a key size of %s')
                    ->withStrtr('%s', strval(EncryptionInterface::KEY_LENGTH))
            );
        }
    }

    public function __toString()
    {
        return $this->key;
    }

    public function base64(): string
    {
        return $this->base64;
    }
}
