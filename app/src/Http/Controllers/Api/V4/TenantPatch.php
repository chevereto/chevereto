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
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevereto\Exceptions\NotFoundException;
use Chevereto\Tenants\Tenants;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\string;

#[Response(
    new Status(204, fail: 404),
)]
class TenantPatch extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(string $id): void
    {
        try {
            $this->tenants->editTenant(
                tenantId: $id,
                hostname: $this->bodyParsed()->optional('hostname')?->string(),
                isEnabled: $this->bodyParsed()->optional('is_enabled')?->bool(),
                planId: $this->bodyParsed()->optional('plan_id')?->string(),
                limits: $this->bodyParsed()->optional('limits')?->array(),
                env: $this->bodyParsed()->optional('env')?->array()
            );
        } catch (NotFoundException) {
            throw new ControllerException(
                'Tenant not found',
                404
            );
        }
    }

    public static function acceptBody(): ParameterInterface
    {
        return arrayp(
            hostname: string(),
            is_enabled: bool(),
            plan_id: string(),
            limits: arrayp(),
            env: arrayp()
        )
            ->withMakeOptional()
            ->withOptionalMinimum(1);
    }
}
