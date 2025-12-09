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

#[Response(
    new Status(204, fail: 404),
)]
class TenantDelete extends Controller
{
    public function __construct(
        private Tenants $tenants,
    ) {
    }

    public function __invoke(string $id): void
    {
        try {
            $delete = $this->tenants->deleteTenant(
                tenantId: $id,
                dropTables: $this->bodyParsed()->optional('drop_tables')?->bool() ?? false,
            );
            if ($delete === -2) {
                throw new NotFoundException();
            }
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
            drop_tables: bool(),
        )
            ->withMakeOptional();
    }
}
