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
use Chevereto\Encryption\Encryption;
use Chevereto\Exceptions\NotFoundException;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Stat;
use ErrorException;
use PDO;
use Redis;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Vars\env;

class Tenants
{
    public const TABLES = [
        'tenants_plans',
        'tenants_stats',
        'tenants',
        'tenants_api_keys',
        'tenants_variables',
    ];

    private string $cacheRootPrefix;

    private string $cachePrefix;

    private string $tableRootPrefix;

    public function __construct(
        private DB $db,
        public readonly Redis $redis,
        private Encryption $encryption,
        private WriterInterface $logger = new NullWriter(),
    ) {
        $this->cacheRootPrefix = env()['CHEVERETO_CACHE_KEY_ROOT_PREFIX']; // chv:
        $this->cachePrefix = env()['CHEVERETO_CACHE_KEY_PREFIX']; // chv:<websiteId>:
        $this->tableRootPrefix = env()['CHEVERETO_DB_TABLE_ROOT_PREFIX']; // chv_<websiteId>_
    }

    public function getCacheKey(string $type, string $value): string
    {
        return "{$this->cachePrefix}{$type}:{$value}";
    }

    public function createTenant(
        string $tenantId,
        string $hostname,
        bool $isEnabled,
        ?string $planId = null,
        ?array $limits = null,
        ?array $env = null,
    ): void {
        $this->db::insert(
            table: 'tenants',
            values: [
                'id' => $tenantId,
                'hostname' => $hostname,
                'is_enabled' => (int) $isEnabled,
                'plan_id' => $planId,
                'limits' => is_array($limits)
                    ? json_encode($limits, JSON_THROW_ON_ERROR)
                    : null,
                'env' => is_array($env)
                    ? $this->encryption->encryptEncode(
                        serialize($env)
                    )
                    : null,
            ]
        );
        $this->cacheTenant($tenantId);
        $this->logger->write(
            <<<PLAIN
            Tenant {$tenantId} created

            PLAIN
        );
    }

    public function getTenant(string $tenantId): Tenant
    {
        $row = $this->getTenantRow($tenantId);
        $this->mergeTenant($row);

        return Tenant::fromRow($row);
    }

    public function getTenants(): array
    {
        $rows = $this->getTenantsRows();
        $tenants = [];
        foreach ($rows as &$row) {
            $this->mergeTenant($row);
            if ($row['stats'] !== null) {
                $row['stats'] = json_decode($row['stats'], true);
            }
            $tenants[] = Tenant::fromRow($row);
        }

        return $tenants;
    }

    /**
     * Edit a tenant
     *
     * @param ?string $hostname New hostname or null to skip
     * @param ?bool $isEnabled New isEnabled or null to skip
     * @param ?string $planId New planId or null to skip (empty string to clear)
     * @param ?array $limits New limits or null to skip (empty array to clear)
     * @param ?array $env New env or null to skip (empty array to clear)
     * @param ?string $lastJobAt New lastJobAt or null to skip
     *
     * @throws NotFoundException When tenant not found
     */
    public function editTenant(
        string $tenantId,
        ?string $hostname = null,
        ?bool $isEnabled = null,
        ?string $planId = null,
        ?array $limits = null,
        ?array $env = null,
        ?string $lastJobAt = null,
    ): int|false {
        $data = [];
        if ($hostname !== null) {
            $data['hostname'] = $hostname;
        }
        if ($isEnabled !== null) {
            $data['is_enabled'] = (int) $isEnabled;
        }
        if ($planId !== null) {
            if ($planId === '') {
                $planId = null;
            }
            $data['plan_id'] = $planId;
        }
        if ($limits !== null) {
            if ($limits === []) {
                $limits = null;
            }
            $data['limits'] = $limits === []
                ? null
                : json_encode($limits, JSON_THROW_ON_ERROR);
        }
        if ($env !== null) {
            $data['env'] = $env === []
                ? null
                : $this->encryption->encryptEncode(
                    serialize($env)
                );
        }
        if ($lastJobAt !== null) {
            $data['last_job_at'] = $lastJobAt;
        }
        if ($data === []) {
            return 0;
        }
        $this->getTenantRow($tenantId);
        $result = $this->db::update(
            'tenants',
            $data,
            [
                'id' => $tenantId,
            ]
        );
        if ($result) {
            $this->db::update(
                'tenants',
                [
                    'updated_at' => datetimegmt(),
                ],
                [
                    'id' => $tenantId,
                ]
            );
            $this->cacheTenant($tenantId);
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenantId} updated

                PLAIN
            );
        } else {
            $this->logger->write(
                <<<PLAIN
                NOTICE: No changes to be made

                PLAIN
            );
        }

        return $result;
    }

    /**
     * - -2: Tenant doesn't exists in the database
     * - -1: Tenant deleted but tables were not dropped ($dropTables is false)
     * -  N: the count of dropped tables (chv_tenant_*) if $dropTables is true
     */
    public function deleteTenant(string $tenantId, bool $dropTables): int
    {
        try {
            $tenant = $this->getTenantRow($tenantId);
        } catch (NotFoundException) {
            $tenant = null;
        }
        if ($tenant !== null) {
            $count = $this->db::delete(
                'tenants',
                [
                    'id' => $tenantId,
                ]
            );
            if ($count === 0) {
                $deleted = -2;
            }
        }
        $tenantKey = $this->getCacheKey('tenant', $tenantId);
        $tenantCache = $this->redis->get($tenantKey);
        if ($tenantCache && $tenant === null) {
            $tenant = $tenantCache;
        }
        $hostname = $tenant['hostname'] ?? null;
        if ($hostname !== null) {
            $hostnameKey = $this->getCacheKey('hostname', $hostname);
            $deletedHostnameKeys = $this->redis->del($hostnameKey);
            if ($deletedHostnameKeys) {
                $this->logger->write(
                    <<<PLAIN
                    Tenant {$tenantId} hostname cache {$hostname} deleted

                    PLAIN
                );
            }
        }
        $deletedTenantKeys = $this->redis->del($tenantKey);
        if ($deletedTenantKeys) {
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenantId} cache deleted

                PLAIN
            );
        }
        $tenantCachePattern = $this->cacheRootPrefix . $tenantId . ':*';
        $iterator = null;
        while ($iterator !== 0) {
            $scan = $this->redis->scan($iterator, "{$tenantCachePattern}");
            foreach ($scan as $key) {
                $result = (bool) $this->redis->del($key);
                $status = 'DELETE';
                if ($result === false && ! $this->redis->get($key)) {
                    $status = '   404';
                }
                $this->logger->write(
                    <<<PLAIN
                    * {$status} > {$key}

                    PLAIN
                );
            }
        }
        if ($dropTables) {
            $likePattern = "{$this->tableRootPrefix}{$tenantId}_%";
            $this->db->query(
                <<<SQL
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name LIKE :like_pattern;
                SQL
            );
            $this->db->bind(':like_pattern', $likePattern);
            $tables = $this->db->fetchAll(PDO::FETCH_COLUMN);
            $dropSql = '';
            foreach ($tables as $table) {
                $dropSql .= <<<SQL
                DROP TABLE IF EXISTS `{$table}`;

                SQL;
            }
            if ($dropSql === '') {
                return $deleted ?? 0;
            }
            $dropSql = <<<SQL
            SET FOREIGN_KEY_CHECKS=0;
            {$dropSql}
            SET FOREIGN_KEY_CHECKS=1;

            SQL;
            $this->db->query($dropSql);
            if ($this->db->exec()) {
                return count($tables);
            }
        }
        $deleted ??= -1;
        $message = match (true) {
            $deleted === -2 => <<<PLAIN
            Tenant not found in database

            PLAIN,
            $deleted === -1 => <<<PLAIN
            Tenant {$tenantId} deleted

            PLAIN,
            $deleted >= 0 => <<<PLAIN
            Tenant deleted, tables dropped: {$deleted}

            PLAIN,
            default => null,
        };
        if ($message !== null) {
            $this->logger->write($message);
        }

        return $deleted;
    }

    /**
     * Returns the tenant cacheable representation where `env` is encrypted.
     *
     * @throws NotFoundException When tenant is not found
     */
    public function getTenantCacheable(string $tenantId): array
    {
        $tenant = $this->getTenantRow($tenantId);
        $this->mergeTenantCacheable($tenant);

        return $tenant;
    }

    public function getTenantsIds(array $where = [], array $sort = []): array
    {
        return $this->db::get(
            table: 'tenants',
            where: $where,
            sort: $sort,
            fetch_style: PDO::FETCH_COLUMN,
        );
    }

    public function getTenantsRows(): array
    {
        $tableTenants = $this->db->getTable('tenants');
        $tableTenantsPlans = $this->db->getTable('tenants_plans');
        $tableTenantsStats = $this->db->getTable('tenants_stats');

        return $this->db->queryFetchAll(
            <<<SQL
            SELECT
                t.id id,
                t.hostname hostname,
                NULLIF(
                    JSON_MERGE_PATCH(
                        COALESCE(tp.limits, JSON_OBJECT()),
                        COALESCE(t.limits, JSON_OBJECT())
                    ),
                    JSON_OBJECT()
                ) AS limits,
                tp.env plan_env,
                t.env env,
                t.is_enabled is_enabled,
                t.plan_id plan_id,
                t.created_at created_at,
                t.updated_at updated_at,
                t.last_job_at last_job_at,
                (
                    SELECT
                        JSON_OBJECT(
                            'users', s.users,
                            'files', s.files,
                            'albums', s.albums,
                            'tags', s.tags,
                            'cron_time', s.cron_time,
                            'file_views', s.file_views,
                            'album_views', s.album_views,
                            'file_likes', s.file_likes,
                            'album_likes', s.album_likes,
                            'storage_used', s.storage_used,
                            'admins', s.admins,
                            'managers', s.managers,
                            'pages', s.pages,
                            'storages', s.storages,
                            'categories', s.categories,
                            'login_providers', s.login_providers
                        ) AS stats
                    FROM `{$tableTenantsStats}` AS s
                    WHERE s.tenant_id = t.id
                ) AS stats
            FROM `{$tableTenants}` AS t
            LEFT JOIN `{$tableTenantsPlans}` AS tp
                ON tp.id = t.plan_id;
            SQL
        );
    }

    public function getTenantStats(string $tenantId): ?array
    {
        $statQuery = Stat::getStatQuery(
            $this->tableRootPrefix . $tenantId . '_'
        );
        $this->db->query($statQuery);
        $this->db->exec();

        return $this->db->fetchSingle() ?: null;
    }

    /**
     * Upsert tenant stats
     */
    public function refreshTenantStats(
        string $tenantId,
    ): void {
        $tableTenantsStats = $this->db->getTable('tenants_stats');
        $stats = $this->getTenantStats($tenantId);
        if ($stats === null) {
            DB::delete(
                'tenants_stats',
                [
                    'tenant_id' => $tenantId,
                ]
            );
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenantId} stats cleared

                PLAIN
            );

            return;
        }
        $columns = array_merge(['tenant_id'], array_keys($stats));
        $colsSql = implode(
            ', ',
            array_map(fn ($c) => "`{$c}`", $columns)
        );
        $placeholders = implode(
            ', ',
            array_map(fn ($c) => ":{$c}", $columns)
        );
        $updateParts = [];
        foreach ($columns as $col) {
            if ($col === 'tenant_id') {
                continue;
            }
            $updateParts[] = <<<SQL
            `{$col}` = VALUES(`{$col}`)
            SQL;
        }
        $updateSql = implode(', ', $updateParts);
        $sql = <<<SQL
        INSERT INTO `{$tableTenantsStats}` ({$colsSql})
            VALUES ({$placeholders})
        ON DUPLICATE KEY UPDATE {$updateSql};

        SQL;
        $this->db->query($sql);
        $this->db->bind(':tenant_id', $tenantId);
        foreach ($stats as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        $this->db->exec();
        $this->logger->write(
            <<<PLAIN
            Tenant {$tenantId} stats refreshed

            PLAIN
        );
    }

    public function cache(): void
    {
        foreach ($this->getTenantsRows() as $tenant) {
            $tenantKey = $this->getCacheKey('tenant', $tenant['id']);
            $this->mergeTenantCacheable($tenant);
            $cached = $this->redis->get($tenantKey);
            if ($cached) {
                try {
                    /** @var array $cached */
                    $cachedHostname = $cached['hostname'];
                    if ($cachedHostname !== $tenant['hostname']) {
                        $cachedKey = $this->getCacheKey('hostname', $cachedHostname);
                        $this->redis->del($cachedKey);
                    }
                } catch (ErrorException) {
                }
            }
            $this->cacheTenantArray($tenant);
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenant['id']} cached

                PLAIN
            );
        }
    }

    public function cacheTenant(string $tenantId): void
    {
        $this->cacheTenantArray(
            $this->getTenantCacheable($tenantId)
        );
    }

    public function getTenantsForPlan(string $planId): array
    {
        return $this->db::get(
            'tenants',
            [
                'plan_id' => $planId,
            ],
            fetch_style: PDO::FETCH_COLUMN,
        );
    }

    public function createPlan(
        string $planId,
        ?array $limits = null,
        ?array $env = null,
    ): void {
        $this->db::insert(
            'tenants_plans',
            [
                'id' => $planId,
                'limits' => is_array($limits)
                    ? json_encode($limits, JSON_THROW_ON_ERROR)
                    : null,
                'env' => $this->encryption->encryptEncode(
                    serialize($env)
                ),
            ]
        );
        $this->logger->write(
            <<<PLAIN
            Tenant plan {$planId} created

            PLAIN
        );
    }

    public function getPlan(string $planId): TenantPlan
    {
        $cacheable = $this->getPlanRow($planId);
        if ($cacheable['limits']) {
            $cacheable['limits'] = json_decode(
                $cacheable['limits'],
                true
            );
        }
        if ($cacheable['env'] !== null) {
            $cacheable['env'] = unserialize(
                $this->encryption->decodeDecrypt($cacheable['env']),
            );
        }

        return new TenantPlan(
            id: $cacheable['id'],
            limits: $cacheable['limits'],
            env: $cacheable['env'],
            createdAt: $cacheable['created_at'],
            updatedAt: $cacheable['updated_at'],
        );
    }

    /**
     * Edit a tenant plan
     * @param ?array $limits New limits or null to skip (empty array to clear)
     * @param ?array $env New env or null to skip (empty array to clear)
     */
    public function editPlan(
        string $planId,
        ?array $limits = null,
        ?array $env = null,
    ): int|false {
        $data = [];
        if ($limits !== null) {
            $data['limits'] = $limits === []
                ? null
                : json_encode($limits, JSON_THROW_ON_ERROR);
        }
        if ($env !== null) {
            $data['env'] = $env === []
                ? null
                : $data['env'] = $this->encryption->encryptEncode(
                    serialize($env)
                );
        }
        $result = $this->db::update(
            'tenants_plans',
            $data,
            [
                'id' => $planId,
            ]
        );
        if ($result == 0) {
            $this->logger->write(
                <<<PLAIN
                NOTICE: No changes to be made

                PLAIN
            );
        } else {
            $this->logger->write(
                <<<PLAIN
                Tenant plan {$planId} updated

                PLAIN
            );
        }
        if (! $result) {
            return $result;
        }
        $affected = $this->getTenantsForPlan($planId);
        foreach ($affected as $tenantId) {
            $this->cacheTenant($tenantId);
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenantId} cache updated

                PLAIN
            );
        }

        return $result;
    }

    public function deletePlan(
        string $planId
    ): int {
        $affected = $this->getTenantsForPlan($planId);
        $count = $this->db::delete(
            'tenants_plans',
            [
                'id' => $planId,
            ]
        );
        $this->logger->write(
            match (true) {
                $count === 0 => <<<PLAIN
                Tenant plan not found

                PLAIN,
                default => <<<PLAIN
                Tenant plan {$planId} deleted

                PLAIN
            }
        );
        foreach ($affected as $tenantId) {
            $this->cacheTenant($tenantId);
            $this->logger->write(
                <<<PLAIN
                Tenant {$tenantId} updated

                PLAIN
            );
        }

        return $count;
    }

    /**
     * @throws NotFoundException When tenant plan not found
     */
    public function getPlanRow(string $tenantPlanId): array
    {
        $tableTenantsPlans = $this->db->getTable('tenants_plans');

        $this->db->query(
            <<<SQL
            SELECT
                tp.id id,
                tp.limits limits,
                tp.env env,
                tp.created_at created_at,
                tp.updated_at updated_at
            FROM `{$tableTenantsPlans}` AS tp
            WHERE tp.id = :id;
            SQL
        );
        $this->db->bind(':id', $tenantPlanId);

        return $this->db->fetchSingle()
            ?: throw new NotFoundException('Tenant not found');
    }

    public function getPlans(?callable $callback = null): array
    {
        $plans = $this->db->get(
            'tenants_plans',
            [],
            clause: 'AND',
        );
        foreach ($plans as &$plan) {
            if ($plan['limits']) {
                $plan['limits'] = json_decode($plan['limits'], true);
            }
            if ($plan['env']) {
                $plan['env'] = unserialize(
                    $this->encryption->decodeDecrypt($plan['env'])
                );
            }
            if (is_callable($callback)) {
                $callback($plan);
            }
        }

        return $plans;
    }

    public function getTenantRow(string $tenantId): array
    {
        $tableTenants = $this->db::getTable('tenants');
        $tableTenantsPlans = $this->db::getTable('tenants_plans');
        $tableTenantsStats = $this->db::getTable('tenants_stats');
        $this->db->query(
            <<<SQL
            SELECT
                t.id id,
                t.hostname hostname,
                NULLIF(
                    JSON_MERGE_PATCH(
                        COALESCE(tp.limits, JSON_OBJECT()),
                        COALESCE(t.limits, JSON_OBJECT())
                    ),
                    JSON_OBJECT()
                ) AS limits,
                tp.env plan_env,
                t.env env,
                t.is_enabled is_enabled,
                t.plan_id plan_id,
                t.created_at created_at,
                t.updated_at updated_at,
                t.last_job_at last_job_at,
                (
                    SELECT JSON_OBJECT(
                        'users', s.users,
                        'files', s.files,
                        'albums', s.albums,
                        'tags', s.tags,
                        'cron_time', s.cron_time,
                        'file_views', s.file_views,
                        'album_views', s.album_views,
                        'file_likes', s.file_likes,
                        'album_likes', s.album_likes,
                        'storage_used', s.storage_used,
                        'admins', s.admins,
                        'managers', s.managers,
                        'pages', s.pages,
                        'storages', s.storages,
                        'categories', s.categories,
                        'login_providers', s.login_providers
                    ) AS stats
                    FROM `{$tableTenantsStats}` AS s
                    WHERE s.tenant_id = t.id
                ) AS stats
            FROM `{$tableTenants}` AS t
            LEFT JOIN `{$tableTenantsPlans}` AS tp
                ON tp.id = t.plan_id
            WHERE t.id = :id;
            SQL
        );
        $this->db->bind(':id', $tenantId);
        $tenant = $this->db->fetchSingle();
        if (! $tenant) {
            throw new NotFoundException('Tenant not found');
        }
        if ($tenant['stats'] !== null) {
            $tenant['stats'] = json_decode($tenant['stats'], true);
        }

        return $tenant;
    }

    private function cacheTenantArray(array $tenant): void
    {
        $hostnameKey = $this->getCacheKey('hostname', $tenant['hostname']);
        $tenantKey = $this->getCacheKey('tenant', $tenant['id']);
        $this->redis->mset(
            [
                $hostnameKey => $tenant['id'],
                $tenantKey => $tenant,
            ]
        );
    }

    private function mergeTenant(array &$row): void
    {
        if ($row['limits']) {
            $row['limits'] = json_decode($row['limits'], true);
        }
        if ($row['plan_env'] !== null) {
            $row['plan_env'] = unserialize(
                $this->encryption->decodeDecrypt($row['plan_env'])
            );
        }
        if ($row['env'] !== null) {
            $row['env'] = unserialize(
                $this->encryption->decodeDecrypt($row['env'])
            );
        }
        if ($row['plan_env'] || $row['env']) {
            $row['env'] = array_merge(
                $row['plan_env'] ?? [],
                $row['env'] ?? []
            );
            $row['env'] = $row['env'] === []
                ? null
                : $row['env'];
        }
        unset($row['plan_env']);
    }

    /**
     * Formats tenant array to be usable on caching where `env` is encrypted.
     */
    private function mergeTenantCacheable(array &$row): void
    {
        $this->mergeTenant($row);
        if (isset($row['env'])) {
            $row['env'] = $this->encryption->encryptEncode(
                serialize($row['env'])
            );
        }
    }
}
