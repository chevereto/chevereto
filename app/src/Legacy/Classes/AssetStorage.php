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

use function Chevereto\Legacy\getSetting;

final class AssetStorage
{
    private static array $storage = [];

    private static ?LocalStorage $localStorage = null;

    private static bool $isLocalLegacy;

    public function __construct(array $storage)
    {
        $storage['id'] ??= 0;
        self::$storage = $storage;
        self::$isLocalLegacy = StorageApis::getApiType((int) $storage['api_id']) == 'local'
            && $storage['bucket'] === PATH_PUBLIC;
        if (($storage['api_id'] ?? false) === 8) {
            self::$localStorage = new LocalStorage($storage);
        }
    }

    public static function getDbSettings(): array
    {
        return [
            'account_id' => getSetting('asset_storage_account_id') ?? '',
            'account_name' => getSetting('asset_storage_account_name') ?? '',
            'api_id' => getSetting('asset_storage_api_id') ?? '',
            'bucket' => getSetting('asset_storage_bucket') ?? '',
            'key' => getSetting('asset_storage_key') ?? '',
            'name' => 'assets',
            'region' => getSetting('asset_storage_region') ?? '',
            'secret' => getSetting('asset_storage_secret') ?? '',
            'server' => getSetting('asset_storage_server') ?? '',
            'service' => getSetting('asset_storage_service') ?? '',
            'url' => getSetting('asset_storage_url') ?? '',
            'use_path_style_endpoint' => getSetting('asset_storage_use_path_style_endpoint') ?? '',
        ];
    }

    public static function getStorage(): array
    {
        return self::$storage;
    }

    public static function isLocalLegacy(): bool
    {
        return self::$isLocalLegacy;
    }

    public static function uploadFiles(array $targets, array $options): array
    {
        return Storage::uploadFiles($targets, self::getStorage(), $options);
    }

    public static function deleteFiles(array $targets): array|false
    {
        return Storage::deleteFiles($targets, self::getStorage());
    }
}
