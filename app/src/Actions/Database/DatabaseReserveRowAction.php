<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Database;

use Chevere\Action\Action;
use function Chevere\DataStructure\data;
use function Chevere\Parameter\integerParameter;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use Chevere\Parameter\Parameters;
use function Chevere\Parameter\parameters;
use Chevereto\Database\Database;

/**
 * Reserves a row in the database.
 *
 * Arguments:
 *
 * ```php
 * table: string,
 * ```
 *
 * Response:
 *
 * ```php
 * id: int,
 * ```
 */
class DatabaseReserveRowAction extends Action
{
    private Database $database;

    public function getContainerParameters(): ParametersInterface
    {
        return new Parameters(
            database: objectParameter(Database::class)
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                id: integerParameter()
            );
    }

    public function run(string $table): array
    {
        // $db->insert row
        return data(
            id: 123
        );
    }
}
