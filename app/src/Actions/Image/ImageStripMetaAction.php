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
use Imagick;
use Intervention\Image\Image;

/**
 * Strip image metadata.
 */
class ImageStripMetaAction extends Action
{
    public function run(Image $image): array
    {
        /** @var Imagick $imagick */
        $imagick = $image->getCore();
        if (!($imagick instanceof Imagick)) {
            return [];
        }
        $profiles = $imagick->getImageProfiles('icc', true);
        $imagick->stripImage();
        // @codeCoverageIgnoreStart
        if (!empty($profiles)) {
            $imagick->profileImage('icc', $profiles['icc']);
        }
        // @codeCoverageIgnoreEnd
        $image->save();

        return [];
    }
}
