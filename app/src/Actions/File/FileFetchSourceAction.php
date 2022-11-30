<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\File;

use Chevere\Action\Action;
use function Chevere\DataStructure\data;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;
use Chevere\Serialize\Deserialize;
use function Chevereto\Encoding\assertBase64;
use function Chevereto\Encoding\storeDecodedBase64;
use function Chevereto\File\storeDownloadedUrl;
use Laminas\Uri\UriFactory;
use Throwable;

final class FileFetchSourceAction extends Action
{
    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                filepath: stringParameter(),
            );
    }

    public function run(
        #[ParameterAttribute(
            description: 'A binary file, base64 data, or an URL for a file.',
        )]
        string $source
    ): array {
        try {
            $deserialize = new Deserialize($source);
            $filepath = $deserialize->var()['tmp_name'];
        } catch (Throwable) {
            $filepath = tempnam(sys_get_temp_dir(), 'chv.temp');
            $uri = UriFactory::factory($source);
            if ($uri->isValid()) {
                storeDownloadedUrl($source, $filepath);
            } else {
                assertBase64($source);
                storeDecodedBase64($source, $filepath);
            }
        }

        return data(
            filepath: $filepath
        );
    }
}
