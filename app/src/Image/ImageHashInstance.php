<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Image;

use Jenssegers\ImageHash\ImageHash;
use LogicException;
use function Chevere\Message\message;

/**
 * @codeCoverageIgnore
 */
final class ImageHashInstance
{
    private static ?ImageHash $instance;

    public function __construct(ImageHash $imageHash)
    {
        self::$instance = $imageHash;
    }

    public static function get(): ImageHash
    {
        if (! isset(self::$instance)) {
            throw new LogicException(
                message('No `%instance%` instance present', s: ImageHash::class)
            );
        }

        return self::$instance;
    }
}
