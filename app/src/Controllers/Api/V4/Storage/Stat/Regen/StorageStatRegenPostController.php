<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Storage\Stat\Regen;

use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;
use stdClass;

class StorageStatRegenPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Regenerate storage stats.';
    }

    public function run(string $storage_id): array
    {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                storage: objectParameter(stdClass::class, Storage::class),
            );
    }
}
