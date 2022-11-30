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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use Intervention\Image\ImageManager;

/**
 * @codeCoverageIgnore
 */
final class ImageManagerInstance
{
    private static ?ImageManager $instance;

    public function __construct(ImageManager $imageManager)
    {
        self::$instance = $imageManager;
    }

    public static function get(): ImageManager
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                message('No ImageManager instance present')
            );
        }

        return self::$instance;
    }
}
