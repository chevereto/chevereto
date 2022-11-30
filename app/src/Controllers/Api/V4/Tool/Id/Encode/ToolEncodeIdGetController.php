<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Tool\Id\Encode;

use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\stringParameter;
use Chevereto\Controllers\WorkflowController;

class ToolEncodeIdGetController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Retrieve an encoded representation of the Id.';
    }

    public function run(string $id): array
    {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                data: stringParameter(),
            );
    }
}
