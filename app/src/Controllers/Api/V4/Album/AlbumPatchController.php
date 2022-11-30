<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Album;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Album\AlbumPatchWorkflow;

#[RelationWorkflow(AlbumPatchWorkflow::class)]
final class AlbumPatchController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Updates the album.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The identifier.',
            regex: '/\w+/'
        )]
        string $id,
        #[ParameterAttribute(
            description: 'The image identifier.',
            regex: '/\w+/'
        )]
        string $cover_id
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                image: objectParameter(Image::class)
            );
    }
}
