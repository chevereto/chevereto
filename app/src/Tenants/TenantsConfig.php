<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Tenants;

use Redis;
use stdClass;
use Throwable;

/**
 * Generates Traefik dynamic configuration for all tenants.
 */
final class TenantsConfig
{
    public const CF_RANGES = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public function __construct(
        public readonly Redis $redis,
    ) {
    }

    public function getConfig(
        Tenants $tenants,
        string $service,
        int $port,
        array $middleware
    ): array {
        $middlewares = [];
        if (in_array('cf-only', $middleware)) {
            $middlewares['cf-only'] = [
                'ipAllowList' => [
                    'sourceRange' => $this->getCFRanges(),
                ],
            ];
        }
        $rows = $tenants->getTenantsRows();
        $routers = [];
        foreach ($rows as $row) {
            $row = array_merge($row, [
                'limits' => [],
                'env' => [],
            ]);
            $tenant = Tenant::fromRow($row);
            $routers[$tenant->id] = $this->getTenantRouter(
                $tenant,
                $service,
                array_keys($middlewares)
            );
        }

        return [
            'http' => [
                'routers' => $routers,
                'services' => [
                    $service => [
                        'loadBalancer' => [
                            'servers' => [
                                [
                                    'url' => sprintf(
                                        'http://%s:%d',
                                        $service,
                                        $port
                                    ),
                                ],
                            ],
                            'passHostHeader' => true,
                        ],
                    ],
                ],
                'middlewares' => $middlewares,
            ],
        ];
    }

    public function getTenantRouter(Tenant $tenant, string $service, array $middlewares): array
    {
        return [
            'rule' => "Host(`{$tenant->hostname}`)",
            'service' => $service,
            'entryPoints' => ['websecure'],
            'tls' => new stdClass(),
            'middlewares' => $middlewares,
        ];
    }

    public function getCFRanges(): array
    {
        $cacheKey = 'cf:ip-ranges';
        $cached = $this->redis->get($cacheKey);
        if ($cached !== false) {
            return json_decode($cached, true);
        }

        try {
            $v4 = file_get_contents('https://www.cloudflare.com/ips-v4');
            $v6 = file_get_contents('https://www.cloudflare.com/ips-v6');
            $ranges = array_values(
                array_filter(
                    array_map(
                        'trim',
                        array_merge(
                            explode("\n", $v4),
                            explode("\n", $v6),
                        )
                    )
                )
            );
        } catch (Throwable) {
            return self::CF_RANGES;
        }
        $this->redis->setex($cacheKey, 3600, json_encode($ranges));

        return $ranges;
    }
}
