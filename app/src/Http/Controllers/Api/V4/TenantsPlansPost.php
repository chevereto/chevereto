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
use Throwable;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\string;

#[Response(
    new Status(201, conflict: 409, fail: 400),
    new Header('Content-Type', 'application/json'),
)]
class TenantsPlansPost extends Controller
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
            $this->tenants->createPlan(
                planId: $this->bodyParsed()->required('id')->string(),
                limits: $limits,
                env: $env,
            );
        } catch (Throwable $e) {
            throw new ControllerException(
                'Failed to create tenant plan',
                $e->getCode() === '23000' ? 409 : 404,
            );
        }
    }

    public static function acceptBody(): ParameterInterface
    {
        return arrayp(
            id: string(),
        )->withOptional(
            name: string(),
            limits: arrayp(),
            env: arrayp()
        );
    }
}
