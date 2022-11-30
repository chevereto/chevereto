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

final class AssetConfig
{
    public function __construct(
        private string $accountId = '',
        private string $accountName = '',
        private string $bucket = '',
        private string $key = '',
        private string $region = '',
        private string $secret = '',
        private string $server = '',
        private string $service = '',
        private string $url = '',
        private string $type = 'local',
        private string $name = 'assets',
    ) {
    }

    public function export()
    {
        return [
            'accountId' => $this->accountId,
            'accountName' => $this->accountName,
            'bucket' => $this->bucket,
            'key' => $this->key,
            'region' => $this->region,
            'secret' => $this->secret,
            'server' => $this->server,
            'service' => $this->service,
            'url' => $this->url,
            'type' => $this->type,
            'name' => $this->name,
        ];
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function accountName(): string
    {
        return $this->accountName;
    }

    public function bucket(): string
    {
        return $this->bucket;
    }
    
    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function region(): string
    {
        return $this->region;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function server(): string
    {
        return $this->server;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function url(): string
    {
        return $this->url;
    }
}
