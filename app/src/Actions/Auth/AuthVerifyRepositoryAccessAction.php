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

class AuthVerifyRepositoryAccessAction extends Action
{
    public function getResponseParameters(): ParametersInterface
    {
        return parameters(
            grant: stringParameter(
                description: 'Describes the permission grantee.',
            )
        );
    }

    public function run(
        #[ParameterAttribute(description: 'User id for the user requesting this resource.')]
        string $requesterUserId,
        #[ParameterAttribute(description: 'Repository name to check access.')]
        string $repository,
        #[ParameterAttribute(
            description: 'Permission level to check.',
            regex: '/^(read|write|execute)$/'
        )]
        string $level
    ): array {
        return data(
            grant: 'isAdmin'
        );
    }
}
