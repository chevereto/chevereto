<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Storage;

use Chevere\Action\Action;
use function Chevere\DataStructure\data;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use Chevere\Parameter\Parameters;
use function Chevere\Parameter\parameters;
use Chevereto\Database\Database;
use Chevereto\Storage\Storage;

/**
 * Finds a valid storage to allocate the bytes required.
 *
 * Response parameters:
 *
 * ```php
 * storage: \Chevereto\Interfaces\Storage\StorageInterface,
 * ```
 */
class StorageGetForUserAction extends Action
{
    public function getContainerParameters(): ParametersInterface
    {
        return new Parameters(
            database: objectParameter(Database::class)
        );
    }

    public function run(int $userId, int $bytesRequired): array
    {
        // $adapter = db->query storage for user;

        return data(
            storage: new Storage(__DIR__)
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return parameters(
            storage: objectParameter(
                className: Storage::class
            )
        );
    }
}
