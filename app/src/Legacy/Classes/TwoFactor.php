<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\G\datetimegmt;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use Throwable;

class TwoFactor
{
    public const ENCRYPTED_NAMES = [
        'secret'
    ];

    private Google2FA $google2FA;

    private string $secret;

    public function __construct()
    {
        $this->google2FA = new Google2FA();
        $this->google2FA->setQrcodeService(
            new Chillerlan()
        );
        $this->secret = $this->google2FA->generateSecretKey(16);
    }

    public function google2FA(): Google2FA
    {
        return $this->google2FA;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function withSecret(string $secret): self
    {
        $new = clone $this;
        $new->secret = $secret;

        return $new;
    }

    public function getQRCodeInline(
        string $company,
        string $holder,
        int $size = 500,
    ): string {
        return $this->google2FA->getQRCodeInline(
            company: $company,
            holder: $holder,
            secret: $this->secret,
            size: $size,
        );
    }

    public function verify(string $userOTP): bool
    {
        return $this->google2FA
            ->verify($userOTP, $this->secret);
    }

    public function insert(int $userId): int
    {
        $values = [
            'user_id' => $userId,
            'date_gmt' => datetimegmt(),
            'secret' => $this->secret,
        ];
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_NAMES, $values);
        }
        self::assertSecret($values['secret']);

        return DB::insert('two_factors', $values);
    }

    public static function update(int $id, array $values): int
    {
        $values['date_gmt'] = datetimegmt();
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_NAMES, $values);
        }
        self::assertSecret($values['secret']);

        return DB::update('two_factors', $values, ['id' => $id]);
    }

    protected static function assertSecret(string $secret): void
    {
        if ($secret === '') {
            throw new LogicException(
                message("Secret can't be empty string"),
                600
            );
        }
    }

    public static function delete(int $userId): void
    {
        DB::delete('two_factors', ['user_id' => $userId]);
    }

    public static function hasFor(int $userId): bool
    {
        return self::getFor($userId) !== [];
    }

    public static function get(int $id, string $by = 'id'): array
    {
        try {
            $get = DB::get('two_factors', [$by => $id], 'AND', ['field' => 'id', 'order' => 'desc'])[0]
                ?? null;
        } catch (Throwable) {
            return [];
        }

        $return = DB::formatRow($get, 'two_factor') ?? [];
        if ($return === []) {
            return $return;
        }
        if (hasEncryption()) {
            $return = decryptValues(self::ENCRYPTED_NAMES, $return);
        }
        self::assertSecret($return['secret']);

        return $return;
    }

    public static function getFor(int $userId): array
    {
        return self::get($userId, 'user_id');
    }

    public static function getSecretFor(int $userId): string
    {
        return self::getFor($userId)['secret'];
    }
}
