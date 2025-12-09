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

class TenantPlan
{
    public function __construct(
        public readonly string $id,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null,
        public readonly ?array $limits = null,
        public readonly ?array $env = null,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            limits: $row['limits'],
            env: $row['env'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'limits' => $this->limits,
            'env' => $this->env,
        ];
    }
}
