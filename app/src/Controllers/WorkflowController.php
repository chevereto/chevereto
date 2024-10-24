<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers;

use Chevere\Controller\Controller;
use Chevere\Workflow\Interfaces\WorkflowInterface;
use Chevere\Workflow\Interfaces\WorkflowProviderInterface;
use LogicException;
use function Chevere\Message\message;

abstract class WorkflowController extends Controller
{
    final public function getWorkflow(): WorkflowInterface
    {
        $relation = $this->relation();
        if ($relation === '') {
            throw new LogicException(
                message: message('Missing workflow provider relationship')
            );
        }
        if (! is_subclass_of($relation, WorkflowProviderInterface::class, true)) {
            throw new LogicException(
                message: message(
                    'Relation` %relation%` is not of type `%type%`',
                    relation: $relation,
                    type: WorkflowProviderInterface::class,
                )
            );
        }
        /** @var WorkflowProviderInterface $workflowProvider */
        $workflowProvider = new $relation();

        return $workflowProvider->getWorkflow();
        // $this->hook('getWorkflow:after', $workflow);
    }
}
