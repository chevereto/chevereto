<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V1\Upload;

use function Chevere\DataStructure\data;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;
use Chevere\Workflow\Attributes\Provider;
use Chevereto\Controllers\WorkflowController;
use Chevereto\Workflows\Legacy\LegacyUploadPostWorkflow;

#[Provider(LegacyUploadPostWorkflow::class)]
final class UploadPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                document: stringParameter()
            );
    }

    public function run(
        #[ParameterAttribute(description: 'A binary file, base64 data, or an URL for an image.')]
        string $source, // try: files
        #[ParameterAttribute(description: 'API V1 key.')]
        string $key,
        #[ParameterAttribute(
            description: 'Response document output format.',
            regex: '/^(json|txt|redirect)$/'
        )]
        string $format = 'json'
    ): array {
        return data();
    }
}
