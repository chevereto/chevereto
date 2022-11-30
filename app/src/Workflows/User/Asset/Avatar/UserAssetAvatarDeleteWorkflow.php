<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\User\Asset\Avatar;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;
use function Chevereto\Workflow\stepVerifyResourceAccess;

final class UserAssetAvatarDeleteWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            user: job(
                'UserGetByUsernameAction',
                username: '${username}',
            ),
            checkout: stepVerifyResourceAccess(
                resource: 'user_avatar',
                level: 'write',
                ownerUserId: '${user:id}'
            ),
            delete: job(
                'DeleteUserAssetAvatarAction',
                id: '${user:id}',
            ),
        );
    }
}
