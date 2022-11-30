<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Export;

use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use stdClass;

class UserExportGetController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Exports the user.';
    }

    public function run(string $username): array
    {
        return [];
    }
    
    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                key: objectParameter(stdClass::class, 'className'),
            );
    }
}
