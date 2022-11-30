<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Image;

use Chevere\Action\Action;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use Chevere\Parameter\Parameters;
use Chevereto\Database\Database;

/**
 * Insert the image in the database.
 */
class ImageInsertAction extends Action
{
    private Database $database;

    public function getContainerParameters(): ParametersInterface
    {
        return new Parameters(
            database: objectParameter(Database::class)
        );
    }

    public function run(
        int $id,
        int $expires,
        int $userId,
        int $albumId,
    ): array {
        // TODO: DB inserting
        return [];
    }
}
