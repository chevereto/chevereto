<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\User\Follow;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;
use function Chevereto\Workflow\stepVerifyResourceAccess;

final class UserFollowPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            user: job(
                'UserGetByUsernameAction',
                username: '${username}',
            ),
            checkout: stepVerifyResourceAccess(
                resource: 'user_follow',
                level: 'write',
                ownerUserId: '${user:id}'
            ),
            step: job(
                'UserFollowInsertAction',
                userId: '${user_id}',
                userIdToFollow: '${user_id_to_follow}',
            )
        );
    }
}
