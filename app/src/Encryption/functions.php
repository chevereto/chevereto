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
use Chevere\Throwable\Exceptions\LogicException;
use Chevereto\Encryption\Interfaces\EncryptionInterface;
use Chevereto\Encryption\Interfaces\KeyInterface;
use function Chevereto\Vars\env;
use phpseclib3\Crypt\Random;
use Throwable;

function assertNonce(string $nonce): void
{
    if (!isValidNonce($nonce)) {
        throw new InvalidArgumentException(
            message('Requires a nonce size of %s')
                ->withStrtr('%s', strval(EncryptionInterface::NONCE_LENGTH))
        );
    }
}

function isValidNonce(string $nonce): bool
{
    return strlen($nonce) === EncryptionInterface::NONCE_LENGTH;
}

function randomNonce(): string
{
    return Random::string(EncryptionInterface::NONCE_LENGTH);
}

function randomKey(): KeyInterface
{
    return new Key(
        base64_encode(
            Random::string(EncryptionInterface::KEY_LENGTH)
        )
    );
}

function encryption(): EncryptionInterface
{
    try {
        return EncryptionInstance::get();
    } catch (Throwable) {
        $base64 = env()['CHEVERETO_ENCRYPTION_KEY'] ?? '';
        new EncryptionInstance(
            $base64 === ''
                ? new NullEncryption()
                : new Encryption(
                    new Key($base64)
                )
        );

        return EncryptionInstance::get();
    }
}

function assertEncryption(): void
{
    if (!hasEncryption()) {
        throw new LogicException(
            message('Encryption is not enabled, set the %s environment variable to use encryption.')
                ->withStrong('%s', 'CHEVERETO_ENCRYPTION_KEY')
        );
    }
}

function hasEncryption(): bool
{
    return !(encryption() instanceof NullEncryption);
}

/**
 * @return string A base64 encoded encrypted string with a nonce.
 */
function encrypt(string $plainText): string
{
    assertEncryption();
    $encode = new Encode(encryption()->withRandomNonce());

    return $encode->encrypt($plainText);
}

function decrypt(string $base64NonceCipherText): string
{
    assertEncryption();
    $decode = new Decode($base64NonceCipherText);

    return encryption()
        ->withNonce($decode->nonce())
        ->decrypt($decode->cipherText());
}

function decryptValues(array $encryptedKeys, array $keyValues): array
{
    return mb_convert_encoding(
        cipherValues($encryptedKeys, $keyValues, function (string $text) {
            return decrypt($text);
        }),
        'UTF-8'
    ) ?: [];
}

function encryptValues(array $encryptedKeys, array $keyValues): array
{
    return cipherValues($encryptedKeys, $keyValues, function (string $text) {
        return encrypt($text);
    });
}

function cipherValues(array $encryptedKeys, array $keyValues, callable $fn): array
{
    foreach ($encryptedKeys as $key) {
        $value = $keyValues[$key] ?? '';
        if ($value !== '') {
            try {
                $cipher = $fn($value);
                $keyValues[$key] = $cipher;
            } catch (Throwable) {
            }
        }
    }

    return $keyValues;
}
