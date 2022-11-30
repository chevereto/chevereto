<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Auth;

use Chevere\Action\Action;
use function Chevere\DataStructure\data;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;

class AuthVerifyResourceAccessAction extends Action
{
    public function run(
        #[ParameterAttribute(
            description: 'User id for the user requesting this resource.'
        )]
        int $requesterUserId,
        #[ParameterAttribute(
            description: 'User id for the owner of the resource.'
        )]
        int $ownerUserId,
        #[ParameterAttribute(
            description: 'Resource name to check access.'
        )]
        string $resource,
        #[
        ParameterAttribute(
            description: 'Permission level to check.',
            regex: '/^(read|write|execute)$/'
        )]
        string $level
    ): array {
        return data(
            grant: 'isAdmin'
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return parameters(
            grant: stringParameter(
                description: 'Describes the permission grantee.',
            )
        );
    }
}
