<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\Ban\Ip;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;

final class BanIpPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            checkManager: job(
                'CheckManagerAction',
            ),
            validateIp: job(
                'ValidateIpAction',
                ip: '${ip}',
            ),
            validateIpNotBanned: job(
                'ValidateIpNotBannedAction',
                ip: '${ip}',
            ),
            insert: job(
                'InsertBanIpAction',
                ip: '${ip}',
                expires: '${expires}',
                message: '${message}',
            ),
        );
    }
}
