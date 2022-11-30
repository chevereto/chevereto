<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Database;

/**
 * Describes the component in charge of providing multiple entity I/O interaction.
 */
interface EntitiesIoInterface
{
    public function __construct(Database $database);

    /**
     * Defines the table name.
     */
    public function table(): string;

    /**
     * Select the entities for the given values (all).
     *
     * @return array Raw associative result.
     */
    public function selectWhereAllValues(array $columns = ['*'], string ...$values): array;

    /**
     * Select the entities for the given values (any).
     *
     * @return array Raw associative result.
     */
    public function selectWhereAnyValues(array $columns = ['*'], string ...$values): array;
}
