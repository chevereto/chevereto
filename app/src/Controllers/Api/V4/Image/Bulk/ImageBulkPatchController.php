<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Image\Bulk;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\ArrayParameter;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Image\Bulk\ImageBulkPatchWorkflow;

#[RelationWorkflow(ImageBulkPatchWorkflow::class)]
class ImageBulkPatchController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Bulk image edit.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'Comma-separated list of images to edit.',
            regex: '/^\w+(,+\w+)*$/'
        )]
        string $image_ids,
        string $category_id = '',
        string $is_approved = '',
        string $is_nsfw = '',
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                edited: new ArrayParameter(),
                failed: new ArrayParameter(),
            );
    }
}
