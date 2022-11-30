<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Image;

use Chevere\Controller\Controller;
use function Chevere\DataStructure\data;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Workflows\Image\ImagePostWorkflow;

final class ImagePostController extends Controller
{
    public function run(
        #[ParameterAttribute(
            description: 'A binary file, base64 data, or an URL for an image.',
            // try: 'files'
        )]
        string $image,
        string $album_id = '',
    ): array {
        $workflow = (new ImagePostWorkflow())->getWorkflow();
        // $source
        // $mimes
        // $max_bytes
        // $min_bytes
        // $max_width
        // $max_height
        // $min_width
        // $min_height
        // $ip
        // $ip_version
        // $user_id
        // $table
        // $name
        // $naming
        // $path
        // $upload_filepath
        // $expires
        // $album_id
        return data();
    }
    
    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                image: objectParameter(
                    className: Image::class
                )
            );
    }
}
