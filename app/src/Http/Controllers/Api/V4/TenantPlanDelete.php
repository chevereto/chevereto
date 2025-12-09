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
use Chevere\Http\Status;
use Chevereto\Exceptions\NotFoundException;
use Chevereto\Tenants\Tenants;

#[Response(
    new Status(204, fail: 404),
)]
class TenantPlanDelete extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(string $id): void
    {
        try {
            $delete = $this->tenants->deletePlan(
                planId: $id,
            );
            if ($delete === 0) {
                throw new NotFoundException();
            }
        } catch (NotFoundException) {
            throw new ControllerException(
                'Tenant not found',
                404
            );
        }
    }
}
