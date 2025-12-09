<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Tenants;

use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\string;
use function Chevere\Parameter\unionNull;

class Tenant
{
    public function __construct(
        public readonly string $id,
        public readonly string $hostname,
        public readonly bool $isEnabled,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null,
        public readonly ?string $planId = null,
        public readonly ?array $limits = null,
        public readonly ?array $env = null,
        public readonly ?array $stats = null,
        public readonly ?string $lastJobAt = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            hostname: $row['hostname'],
            isEnabled: (bool) $row['is_enabled'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            planId: $row['plan_id'],
            limits: $row['limits'],
            env: $row['env'],
            stats: $row['stats'],
            lastJobAt: $row['last_job_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'hostname' => $this->hostname,
            'is_enabled' => $this->isEnabled,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'plan_id' => $this->planId,
            'limits' => $this->limits,
            'env' => $this->env,
            'stats' => $this->stats,
            'last_job_at' => $this->lastJobAt,
        ];
    }

    public static function parameter(): ArrayParameterInterface
    {
        return arrayp(
            id: string(),
            hostname: string(),
            is_enabled: bool(),
            created_at: string(),
            updated_at: unionNull(string()),
            plan_id: unionNull(string()),
            limits: unionNull(arrayp()),
            env: unionNull(arrayp()),
            stats: unionNull(arrayp()),
            last_job_at: unionNull(string()),
        );
    }
}
