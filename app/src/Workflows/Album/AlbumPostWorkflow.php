<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\Album;

use Chevere\Action\Action;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;

final class AlbumPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            step: job(
                Action::class,
                parameter: '${variable}',
            )
        );
    }
}
