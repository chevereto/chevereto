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

use Chevere\Writer\Interfaces\WriterInterface;
use Chevere\Writer\NullWriter;
use Chevereto\Legacy\Classes\DB;
use InvalidArgumentException;
use Throwable;
use function Chevere\Standard\randomString;
use function Chevereto\Legacy\passwordHash;
use function Chevereto\Vars\env;

class TenantsApiKey
{
    public function __construct(
        private DB $db,
        private WriterInterface $logger = new NullWriter(),
    ) {
    }

    public static function generate(int $id): string
    {
        $random = randomString(64);
        $data = "{$id}_{$random}";
        $signature = hash_hmac(
            algo: 'sha256',
            data: $data,
            key: env()['CHEVERETO_TENANTS_API_KEY_SECRET']
        );

        return 'chv_'
            . $data
            . '_'
            . $signature;
    }

    public static function hash(string $key): string
    {
        return passwordHash($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assert(string $key): array
    {
        $explode = explode('_', $key);
        if (count($explode) !== 4) {
            throw new InvalidArgumentException('Invalid API key format');
        }
        $chv = $explode[0];
        if ($chv !== 'chv') {
            throw new InvalidArgumentException('Invalid API key prefix');
        }
        $id = $explode[1];
        $random = $explode[2];
        $signature = $explode[3];
        $generated = hash_hmac(
            'sha256',
            "{$id}_{$random}",
            env()['CHEVERETO_TENANTS_API_KEY_SECRET']
        );
        if (! hash_equals($generated, $signature)) {
            throw new InvalidArgumentException('Invalid API key signature');
        }
        $get = $this->get($id);
        if ($get === []) {
            throw new InvalidArgumentException('API key not found or expired');
        }
        $verify = password_verify($key, $get['hash']);
        if ($verify === false) {
            throw new InvalidArgumentException('API key verification failed');
        }

        return [
            'id' => $get['id'],
            'name' => $get['name'],
            'created_at' => $get['created_at'],
            'expires_at' => $get['expires_at'],
        ];
    }

    public function insert(?string $name, ?string $expiresAt): string
    {
        $insert = $this->db::insert(
            'tenants_api_keys',
            [
                'name' => $name,
                'expires_at' => $expiresAt,
                'hash' => '',
            ]
        );
        $key = self::generate($insert);
        $this->db::update(
            'tenants_api_keys',
            [
                'hash' => self::hash($key),
            ],
            [
                'id' => $insert,
            ]
        );

        return $key;
    }

    public function remove(?int $id, ?string $name): int
    {
        $conditions = [];
        if ($id !== null) {
            $conditions['id'] = $id;
        }
        if ($name !== null) {
            $conditions['name'] = $name;
        }
        if ($conditions === []) {
            throw new InvalidArgumentException('At least one condition must be provided to remove an API key.');
        }
        $result = $this->db::delete(
            'tenants_api_keys',
            $conditions
        );
        if ($result) {
            $this->logger->write(
                <<<PLAIN
                API key successfully removed

                PLAIN
            );
        } else {
            $this->logger->write(
                <<<PLAIN
                No API key found matching the provided conditions

                PLAIN
            );
        }

        return $result;
    }

    public function get(int $id): array
    {
        $tenantsApiKeysTable = $this->db::getTable('tenants_api_keys');

        try {
            $this->db->query(
                <<<PLAIN
                SELECT * FROM {$tenantsApiKeysTable}
                WHERE id = :id
                AND (expires_at IS NULL OR expires_at > NOW())
                LIMIT 1
                PLAIN
            );
            $this->db->bind(':id', $id);

            return $this->db->fetchSingle() ?? [];
        } catch (Throwable) {
            return [];
        }
    }
}
