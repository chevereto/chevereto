<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Follow;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevereto\Controllers\WorkflowController;

class UserFollowPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Follows the user.';
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
