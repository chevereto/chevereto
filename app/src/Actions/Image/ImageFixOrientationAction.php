<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Image;

use Chevere\Action\Action;
use Intervention\Image\Image;

/**
 * Fix the image orientation based on Exif Orientation (if any, if needed).
 */
class ImageFixOrientationAction extends Action
{
    public function run(Image $image): array
    {
        $image->orientate()->save();

        return [];
    }
}
