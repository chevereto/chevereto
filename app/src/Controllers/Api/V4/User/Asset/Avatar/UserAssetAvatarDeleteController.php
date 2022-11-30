<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Asset\Avatar;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\User\Asset\Avatar\UserAssetAvatarDeleteWorkflow;

#[RelationWorkflow(UserAssetAvatarDeleteWorkflow::class)]
final class UserAssetAvatarDeleteController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Delete the user avatar image resource.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The username.',
            regex: '/\w+/'
        )]
        string $username
    ): array {
        return [];
    }
}
