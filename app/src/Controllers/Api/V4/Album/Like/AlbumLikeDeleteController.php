<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Album\Like;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Album\Like\AlbumLikeDeleteWorkflow;

#[RelationWorkflow(AlbumLikeDeleteWorkflow::class)]
final class AlbumLikeDeleteController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Delete album like.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The identifier.',
            regex: '/\w+/'
        )]
        string $id
    ): array {
        return [];
    }
}
