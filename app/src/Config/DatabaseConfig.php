<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Config;

final class DatabaseConfig
{
    public function __construct(
        private string $driver = 'mysql',
        private string $host = 'localhost',
        private string $name = 'chevereto',
        private string $user = 'chevereto',
        private string $pass = 'user_database_password',
        private string $tablePrefix = 'chv_',
        private array $pdoAttrs = [],
        private int $port = 3306,
    ) {
    }

    public function export(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'name' => $this->name,
            'user' => $this->user,
            'pass' => $this->pass,
            'tablePrefix' => $this->tablePrefix,
            'pdoAttrs' => $this->pdoAttrs,
            'port' => $this->port,
        ];
    }

    public function driver(): string
    {
        return $this->driver;
    }

    public function host(): string
    {
        return $this->host;
    }
    
    public function name(): string
    {
        return $this->name;
    }

    public function pass(): string
    {
        return $this->pass;
    }

    public function pdoAttrs(): array
    {
        return $this->pdoAttrs;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function tablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function user(): string
    {
        return $this->user;
    }
}
