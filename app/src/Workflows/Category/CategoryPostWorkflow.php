<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\Category;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Workflow\BaseWorkflow;

final class CategoryPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            checkAdmin: job(
                'CheckAdminAction',
            ),
            checkAvailableUrlKey: job(
                'CheckAvailableUrlKey',
                urlKey: '${url_key}',
            ),
            insert: job(
                'InsertCategoryAction',
                name: '${name}',
                urlKey: '${url_key}',
                description: '${description}',
            )
        );
    }
}
