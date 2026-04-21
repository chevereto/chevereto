<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Http\Controllers\Api\V4;

use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Header;
use Chevere\Http\Status;
use Chevereto\Tenants\Tenants;
use Chevereto\Tenants\TenantsConfig;
use function Chevereto\Vars\env;

#[Response(
    new Status(200),
    new Header('Content-Type', 'application/json'),
)]
class TenantsConfigTraefikGet extends Controller
{
    public function __construct(
        private Tenants $tenants,
        private TenantsConfig $tenantsConfig,
    ) {
    }

    public function __invoke(): string
    {
        $return = $this->tenantsConfig->getConfig(
            rootHostname: env()['CHEVERETO_HOSTNAME'],
            tenants: $this->tenants,
            service: env()['CHEVERETO_SERVICE_NAME'],
            port: env()['CHEVERETO_SERVICE_PORT'],
            entryPoint: env()['CHEVERETO_PROXY_ENTRYPOINT'],
            allowedIPs: env()['CHEVERETO_PROXY_IP_ALLOW_LIST'],
        );

        return json_encode($return, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
