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
use Chevere\Throwable\Exceptions\RuntimeException;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Throwable;

function hasExtGd(): bool
{
    return extension_loaded('gd') && function_exists('gd_info');
}

function hasExtImagick(): bool
{
    return extension_loaded('imagick') && class_exists('Imagick');
}

function imageManager(): ImageManager
{
    try {
        return ImageManagerInstance::get();
    } catch (Throwable) {
        $driver = match (true) {
            hasExtImagick() => 'Imagick',
            hasExtGd() => 'Gd',
            default => '',
        };
        if ($driver === '') {
            throw new RuntimeException(
                message: message('No image driver available')
            );
        }
        $manager = new ImageManager(['driver' => $driver]);
        new ImageManagerInstance($manager);

        return ImageManagerInstance::get();
    }
}

function imageHash(): ImageHash
{
    try {
        return ImageHashInstance::get();
    } catch (Throwable) {
        new ImageHashInstance(
            new ImageHash(new DifferenceHash(16))
        );

        return ImageHashInstance::get();
    }
}
