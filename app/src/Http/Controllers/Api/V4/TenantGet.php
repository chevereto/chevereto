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
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Http\Header;
use Chevere\Http\Status;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevereto\Exceptions\NotFoundException;
use Chevereto\Tenants\Tenant;
use Chevereto\Tenants\Tenants;

#[Response(
    new Status(200, fail: 404),
    new Header('Content-Type', 'application/json'),
)]
class TenantGet extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(string $id): array
    {
        try {
            return $this->tenants
                ->getTenant(tenantId: $id)
                ->toArray();
        } catch (NotFoundException) {
            throw new ControllerException(
                'Tenant not found',
                404
            );
        }
    }

    public static function acceptReturn(): ArrayParameterInterface
    {
        return Tenant::parameter();
    }
}
