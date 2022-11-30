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
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Album\AlbumPostWorkflow;

#[RelationWorkflow(AlbumPostWorkflow::class)]
final class AlbumPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Creates an album.';
    }

    public function run(
        string $description,
        string $name,
        string $parent_id,
        string $password,
        string $privacy,
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                album: objectParameter(
                    className: Album::class
                ),
            );
    }
}
