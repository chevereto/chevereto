<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\User;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;
use function Chevereto\Workflow\stepVerifyRepositoryAccess;

final class UserPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            checkout: stepVerifyRepositoryAccess(
                repository: 'users',
                level: 'write',
            ),
            validateUsername: job(
                'ValidateUsernameAction',
                username: '${username}',
            ),
            validateEmail: job(
                'ValidateEmailAction',
                email: '${email}',
            ),
            validatePassword: job(
                'ValidatePasswordAction',
                password: '${password}',
            ),
            validateRole: job(
                'ValidateRoleAction',
                role: '${role}',
            ),
            validateAvailableUsername: job(
                'ValidateAvailableUsernameAction',
                username: '${username}',
            ),
            validateAvailableEmail: job(
                'ValidateAvailableEmailAction',
                email: '${email}'
            ),
            insert: job(
                'InsertUserAction',
                username: '${username}',
                email: '${email}',
                password: '${password}',
                role: '${role}',
            )
        );
    }
}
