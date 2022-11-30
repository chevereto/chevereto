<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevereto\Controllers\WorkflowController;

final class UserGetController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Get an user identified by its id.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The user identifier.',
            regex: '/\w+/',
        )]
        string $id
    ): array {
        return [];
    }
}
