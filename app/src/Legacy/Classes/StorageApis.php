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

use InvalidArgumentException;
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
            'url' => '',
        ],
        9 => [
            'name' => 'S3 compatible',
            'type' => 's3compatible',
            'url' => '',
        ],
        2 => [
            'name' => 'Google Cloud',
            'type' => 'gcloud',
            'url' => '',
        ],
        3 => [
            'name' => 'Microsoft Azure',
            'type' => 'azure',
            'url' => '',
        ],
        10 => [
            'name' => 'Alibaba Cloud OSS',
            'type' => 'oss',
            'url' => '',
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
            'url' => '',
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
        if (! (bool) env()['CHEVERETO_ENABLE_EXTERNAL_STORAGE_PROVIDERS']) {
            $enabled_apis = [8];
            $enabled = [];
            foreach ($apis as $id => &$api) {
                if (! in_array($id, $enabled_apis)) {
                    $api['disabled'] = true;

                    continue;
                }
                $enabled[$id] = $api;
                unset($apis[$id]);
            }
            $apis = $enabled + $apis;
        }
        if (! (bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) {
            unset($apis[8]);
        }

        return $apis;
    }

    public static function getAnon(
        int $api_id,
        string $name,
        string $url,
        string $bucket,
        ?string $key = null,
        ?string $secret = null,
        ?string $region = null,
        ?string $server = null,
        ?string $service = null,
        ?string $account_id = null,
        ?string $account_name = null,
        ?bool $use_path_style_endpoint = null
    ): array {
        $enabled = self::getEnabled();
        if (! array_key_exists($api_id, $enabled)) {
            throw new InvalidArgumentException('Storage API not available.', 1001);
        }

        return [
            'api_id' => $api_id,
            'name' => $name,
            'url' => rtrim($url, '/') . '/',
            'bucket' => $api_id == 8
                ? (rtrim($bucket, '/') . '/')
                : $bucket,
            'region' => $region,
            'server' => $server,
            'service' => $service,
            'account_id' => $account_id,
            'account_name' => $account_name,
            'key' => $key,
            'secret' => $secret,
            'id' => null,
            'is_https' => str_starts_with($url, 'https'),
            'is_active' => true,
            'capacity' => null,
            'space_used' => null,
            'use_path_style_endpoint' => $use_path_style_endpoint,
        ];
    }

    public static function getApiType(int $api_id): string
    {
        return self::$apis[$api_id]['type']
            ?? throw new InvalidArgumentException('Invalid Storage API ID');
    }
}
