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

use Chevereto\Traits\Instance\AssertNoInstanceTrait;

final class AssetStorage
{
    use AssertNoInstanceTrait;

    protected static array $storage = [];

    protected static ?LocalStorage $localStorage = null;

    protected static bool $isLocalLegacy;

    public function __construct(array $storage)
    {
        $this->assertNoInstance();
        self::$storage = $storage;
        self::$isLocalLegacy = StorageApis::getApiType((int) $storage['api_id']) == 'local'
            && PATH_PUBLIC === $storage['bucket'];
        if (($storage['api_id'] ?? false) === 8) {
            self::$localStorage = new LocalStorage($storage);
        }
    }

    public static function getStorage(): array
    {
        return self::$storage;
    }

    public static function isLocalLegacy(): bool
    {
        return self::$isLocalLegacy;
    }

    public static function uploadFiles(array $targets, array $options): void
    {
        Storage::uploadFiles($targets, self::getStorage(), $options);
    }

    public static function deleteFiles(array $targets): void
    {
        Storage::deleteFiles($targets, self::getStorage());
    }
}
