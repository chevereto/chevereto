<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Setting;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use stdClass;

#[RelationWorkflow('')]
class UserSettingPatchController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Updates user settings.';
    }
    
    public function run(
        #[ParameterAttribute(
            description: 'The user identifier.'
        )]
        string $userId
    ): array {
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
