<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflow;

use Chevere\Workflow\Interfaces\JobInterface;
use function Chevere\Workflow\job;
use Chevereto\Actions\Auth\AuthVerifyRepositoryAccessAction;
use Chevereto\Actions\Auth\AuthVerifyResourceAccessAction;

function stepVerifyResourceAccess(
    string $resource,
    string $level,
    string $ownerUserId = '',
    string $requesterUserId = '${REQUESTER_USER_ID}'
): JobInterface {
    return job(
        AuthVerifyResourceAccessAction::class,
        requesterUserId: $requesterUserId,
        resource: $resource,
        level: $level,
        ownerUserId: $ownerUserId
    );
}

function stepVerifyRepositoryAccess(
    string $repository,
    string $level,
    string $requesterUserId = '${REQUESTER_USER_ID}'
): JobInterface {
    return job(
        AuthVerifyRepositoryAccessAction::class,
        requesterUserId: $requesterUserId,
        repository: $repository,
        level: $level,
    );
}
