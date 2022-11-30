<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Legacy\Api\V1;

use Chevere\Action\Action;
use function Chevere\DataStructure\data;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;

class LegacyApiV1OutputAction extends Action
{
    public function run(
        #[ParameterAttribute(regex: '/^(json|txt|redirect)$/')]
        string $format
    ): array {
        return data(
            document: 'formatted_document'
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                document: stringParameter()
            );
    }
}
