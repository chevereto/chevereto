<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Intervention\Image\ImageManagerStatic;

class ImageConvert
{
    private string $out;

    public function __construct(
        array|string $source,
        string $to,
        string $destination,
        int $quality = 90
    ) {
        if (!in_array($to, ['jpg', 'jpeg', 'gif', 'png'])) {
            return;
        }
        $image = ImageManagerStatic::make($source);
        $image->encode($to, $quality)->save($destination);
        $this->out = $destination;
    }

    public function out(): string
    {
        return $this->out;
    }
}
