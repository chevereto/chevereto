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
use Chevere\Parameter\ArrayParameter;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use Intervention\Image\Image;
use JeroenDesloovere\XmpMetadataExtractor\XmpMetadataExtractor;

/**
 * Fetch image metadata.
 *
 * Response parameters:
 *
 * ```php
 * exif: array,
 * iptc: array,
 * xmp: array,
 * ```
 */
class ImageFetchMetaAction extends Action
{
    public function run(Image $image): array
    {
        $data = array_fill_keys(['exif', 'iptc', 'xmp'], []);
        $data['exif'] = $image->exif() ?? [];
        $data['iptc'] = $image->iptc() ?? [];
        $xmpDataExtractor = new XmpMetadataExtractor();
        $data['xmp'] = $xmpDataExtractor->extractFromFile($image->basePath());

        return $data;
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                exif: new ArrayParameter(),
                iptc: new ArrayParameter(),
                xmp: new ArrayParameter(),
            );
    }
}
