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

use function Chevereto\Vars\env;

final class StorageApis
{
    private static array $apis = [
        8 => [
            'name' => 'Local',
            'type' => 'local',
            'url' => '',
        ],
        1 => [
            'name' => 'Amazon S3',
            'type' => 's3',
            'url' => 'https://s3.amazonaws.com/<bucket>/',
        ],
        9 => [
            'name' => 'S3 compatible',
            'type' => 's3compatible',
            'url' => '',
        ],
        2 => [
            'name' => 'Google Cloud',
            'type' => 'gcloud',
            'url' => 'https://storage.googleapis.com/<bucket>/',
        ],

        3 => [
            'name' => 'Microsoft Azure',
            'type' => 'azure',
            'url' => 'https://<account>.blob.core.windows.net/<container>/',
        ],
        10 => [
            'name' => 'Alibaba Cloud OSS',
            'type' => 'oss',
            'url' => 'https://<bucket>.<endpoint>/',
        ],
        6 => [
            'name' => 'SFTP',
            'type' => 'sftp',
            'url' => '',
        ],
        5 => [
            'name' => 'FTP',
            'type' => 'ftp',
            'url' => '',
        ],
        7 => [
            'name' => 'OpenStack',
            'type' => 'openstack',
            'url' => '',
        ],
        11 => [
            'name' => 'Backblaze B2 (legacy API)',
            'type' => 'b2',
            'url' => 'https://f002.backblazeb2.com/file/<bucket>/',
        ],
    ];

    public static function getApiId(string $type): int
    {
        foreach (self::$apis as $id => $api) {
            if ($api['type'] === $type) {
                return $id;
            }
        }

        return 0;
    }

    public static function getEnabled(): array
    {
        $apis = self::$apis;
        if (!(bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) {
            unset($apis[8]);
        }

        return $apis;
    }

    public static function getAnon(
        string $type,
        string $name,
        string $url,
        string $bucket,
        ?string $key = null,
        ?string $secret = null,
        ?string $region = null,
        ?string $server = null,
        ?string $service = null,
        ?string $accountId = null,
        ?string $accountName = null
    ): array {
        return [
            'api_id' => self::getApiId($type),
            'name' => $name,
            'url' => rtrim($url, '/') . '/',
            'bucket' => $type == 'local' ? (rtrim($bucket, '/') . '/') : $bucket,
            'region' => $region,
            'server' => $server,
            'service' => $service,
            'account_id' => $accountId,
            'account_name' => $accountName,
            'key' => $key,
            'secret' => $secret,
            'id' => null,
            'is_https' => str_starts_with($url, 'https'),
            'is_active' => true,
            'capacity' => null,
            'space_used' => null,
        ];
    }

    public static function getApiType(int $api_id): string
    {
        return self::$apis[$api_id]['type'];
    }
}
