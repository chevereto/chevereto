<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\User\UserPostWorkflow;

final class UserPostController extends WorkflowController
{
    public function getWorkflowName(): string
    {
        return UserPostWorkflow::class;
    }
    
    public function getDescription(): string
    {
        return 'Creates an user.';
    }

    public function run(
        #[ParameterAttribute(
            regex: '/^[\w]{3,16}$/'
        )]
        string $username,
        string $email,
        #[ParameterAttribute(
            regex: '/^.{6,128}$/'
        )]
        string $password,
        #[ParameterAttribute(
            regex: '/^(user|manager|admin)$/',
        )]
        string $role = 'user'
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                user: objectParameter(
                    className: User::class
                )
            );
    }
}
