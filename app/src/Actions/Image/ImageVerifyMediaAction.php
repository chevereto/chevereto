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
use function Chevere\DataStructure\data;
use Chevere\Message\Interfaces\MessageInterface;
use function Chevere\Message\message;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use function Chevereto\Image\imageHash;
use function Chevereto\Image\imageManager;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Validates an image against the image processing and image dimensions.
 *
 * Response parameters:
 *
 * ```php
 * image: \Intervention\Image\Image,
 * perceptual: string,
 * ```
 */
class ImageVerifyMediaAction extends Action
{
    private int $width = 0;

    private int $height = 0;

    private int $maxWidth = 0;

    private int $maxHeight = 0;

    private int $minWidth = 0;

    private int $minHeight = 0;

    public function run(
        string $filepath,
        int $maxHeight,
        int $maxWidth,
        int $minHeight,
        int $minWidth,
    ): array {
        $image = $this->assertGetImage($filepath);
        $this->width = $image->width();
        $this->height = $image->height();
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        $this->assertMinHeight();
        $this->assertMaxHeight();
        $this->assertMinWidth();
        $this->assertMaxWidth();

        return data(
            image: $image,
            perceptual: imageHash()->hash($filepath)
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                image: objectParameter(
                    className: Image::class
                ),
                perceptual: stringParameter(),
            );
    }

    private function assertGetImage(string $filepath): Image
    {
        try {
            return imageManager()->make($filepath);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                previous: $e,
                code: 1000,
                message: message("Filepath %filepath% provided can't be handled by %manager%")
                    ->withCode('%filepath%', $filepath)
                    ->withCode('%manager%', ImageManager::class)
            );
        }
    }

    private function assertMinHeight(): void
    {
        if ($this->height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('height', $this->height),
                1001
            );
        }
    }

    private function assertMaxHeight(): void
    {
        if ($this->height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('height', $this->height),
                1002
            );
        }
    }

    private function assertMinWidth(): void
    {
        if ($this->width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('width', $this->width),
                1003
            );
        }
    }

    private function assertMaxWidth(): void
    {
        if ($this->width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('width', $this->width),
                1004
            );
        }
    }

    private function getMinExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return message("Image %dimension% %provided% doesn't meet the the minimum required (%required%)")
            ->withCode('%dimension%', $dimension)
            ->withCode('%provided%', (string) $provided)
            ->withCode('%required%', $this->getMinRequired());
    }

    private function getMinRequired(): string
    {
        return (string) $this->minWidth . 'x' . (string) $this->minHeight;
    }

    private function getMaxExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return message('Image %dimension% %provided% exceeds the maximum allowed (%allowed%)')
            ->withCode('%dimension%', $dimension)
            ->withCode('%provided%', (string) $provided)
            ->withCode('%allowed%', $this->getMaxAllowed());
    }

    private function getMaxAllowed(): string
    {
        return (string) $this->maxWidth . 'x' . (string) $this->maxHeight;
    }
}
