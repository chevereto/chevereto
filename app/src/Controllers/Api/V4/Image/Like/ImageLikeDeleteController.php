<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Image\Like;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevereto\Controllers\WorkflowController;

class ImageLikeDeleteController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Delete image like.';
    }

    public function run(
        #[ParameterAttribute(
            description: 'The image identifier.',
            regex: '/\w+/'
        )]
        string $id,
    ): array {
        return [];
    }
}
