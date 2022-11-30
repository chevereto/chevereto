<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Stat\Rebuild;

use Chevereto\Controllers\WorkflowController;

class StatRebuildPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Rebuild stats.';
    }
}
