<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Asset\Background;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\User\Asset\Background\UserAssetBackgroundDeleteWorkflow;

#[RelationWorkflow(UserAssetBackgroundDeleteWorkflow::class)]
final class UserAssetBackgroundDeleteController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Delete the user background image resource.';
    }
}
