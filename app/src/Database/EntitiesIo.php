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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\OutOfBoundsException;
use Chevereto\Database\Traits\GetWhereEqualsTrait;
use Doctrine\DBAL\Result;

/**
 * Provides database I/O for the X entities.
 */
abstract class EntitiesIo implements EntitiesIoInterface
{
    use GetWhereEqualsTrait;

    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    abstract public function table(): string;

    public function selectWhereAllValues(array $columns = ['*'], string ...$values): array
    {
        return $this->selectWhereValues($columns, ...$values);
    }

    public function selectWhereAnyValues(array $columns = ['*'], string ...$values): array
    {
        return $this->selectWhereValues($columns, ...$values);
    }

    protected function selectWhereValues(array $columns = ['*'], string ...$values): array
    {
        $all = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] === 'selectWhereAllValues';
        $queryBuilder = $this->database->getQueryBuilder()
            ->select(...$columns)
            ->from($this->table());
        foreach ($values as $column => $value) {
            $column = (string) $column;
            $where = $this->getWhereEquals($column);
            if ($all) {
                $queryBuilder->andWhere($where);
            } else {
                $queryBuilder->orWhere($where);
            }
            $queryBuilder->setParameter($column, $value);
        }
        /** @var Result $result */
        $result = $queryBuilder->execute();
        $fetch = $result->fetchAllAssociative();
        if ($fetch === false) {
            throw new OutOfBoundsException(
                message: message('No record exists for values provided')
            );
        }

        return $fetch;
    }
}
