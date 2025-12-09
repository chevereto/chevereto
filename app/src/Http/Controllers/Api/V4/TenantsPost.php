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
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevereto\Tenants\Tenants;
use PDOException;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\string;

#[Response(
    new Status(201, conflict: 409, fail: 400),
    new Header('Content-Type', 'application/json'),
)]
class TenantsPost extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(): void
    {
        try {
            $limits = $this->bodyParsed()->optional('limits')?->array() ?? null;
            $env = $this->bodyParsed()->optional('env')?->array() ?? null;
            $this->tenants->createTenant(
                tenantId: $this->bodyParsed()->required('id')->string(),
                hostname: $this->bodyParsed()->required('hostname')->string(),
                isEnabled: $this->bodyParsed()->required('is_enabled')->bool(),
                planId: $this->bodyParsed()->optional('plan_id')?->string() ?? null,
                limits: $limits,
                env: $env,
            );
        } catch (PDOException $e) {
            throw new ControllerException(
                'Failed to create tenant',
                $e->getCode() === '23000' ? 409 : 400
            );
        }
    }

    public static function acceptBody(): ParameterInterface
    {
        return arrayp(
            id: string(),
            hostname: string(),
            is_enabled: bool(),
        )->withOptional(
            plan_id: string(),
            limits: arrayp(),
            env: arrayp()
        );
    }
}
