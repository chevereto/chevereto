<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Image;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Image\ImagePatchWorkflow;

#[RelationWorkflow(ImagePatchWorkflow::class)]
class ImagePatchController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Edit the image resource.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The image identifier.',
            regex: '/\w+/'
        )]
        string $id,
        string $category_id,
        #[ParameterAttribute(
            regex: '/^(0|1)$/'
        )]
        string $is_approved,
        #[ParameterAttribute(
            regex: '/^(0|1)$/'
        )]
        string $is_nsfw
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                image: objectParameter(stdClass::class, Image::class),
            );
    }
}
