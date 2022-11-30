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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

final class Database
{
    private Connection $connection;

    private QueryBuilder $queryBuilder;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->connection);
    }
}
