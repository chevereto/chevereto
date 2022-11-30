<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Storage;

use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;

class StoragePostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Creates a storage.';
    }

    public function run(
        string $account_id,
        string $account_name,
        string $api_id,
        string $bucket,
        string $capacity,
        string $id,
        string $key,
        string $name,
        string $region,
        string $secret,
        string $server,
        string $service,
        string $url,
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                storage: objectParameter(
                    className: Storage::class
                ),
            );
    }
}
