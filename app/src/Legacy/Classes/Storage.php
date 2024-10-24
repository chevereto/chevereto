<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Exception;
use LogicException;
use PDO;
use Throwable;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\assertMaxCount;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\array_filter_array;
use function Chevereto\Legacy\G\check_value;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\get_basename_without_extension;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_file_extension;
use function Chevereto\Legacy\G\get_filename_by_method;
use function Chevereto\Legacy\G\is_https;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Vars\env;
use function Safe\json_encode;

class Storage
{
    public const ENCRYPTED_NAMES = [
        'server',
        'service',
        'account_id',
        'account_name',
        'key',
        'secret',
        'bucket',
    ];

    public static function getSingle(int $var): array
    {
        return self::get(
            [
                'id' => $var,
            ],
            [],
            1
        );
    }

    public static function get(array $values = [], array $sort = [], int $limit = null): array
    {
        if (! isset($values['deleted_at'])) {
            $values['deleted_at'] = null;
        }
        $get = DB::get(
            [
                'table' => 'storages',
                'join' => 'LEFT JOIN ' . DB::getTable('storage_apis') . ' ON '
                    . DB::getTable('storages') . '.storage_api_id = '
                    . DB::getTable('storage_apis') . '.storage_api_id',
            ],
            $values,
            'AND',
            $sort,
            $limit,
            PDO::FETCH_ASSOC,
            [
                'type_chain' => '&',
                'deleted_at' => 'IS',
            ]
        );
        if (isset($get[0]) && is_array($get[0])) {
            foreach ($get as $k => $v) {
                self::formatRowValues($get[$k], $v);
            }
        } elseif (! empty($get)) {
            self::formatRowValues($get);
        }

        return is_array($get) ? $get : [];
    }

    public static function uploadFiles(
        array|string $targets,
        array|int $storage,
        array $options = []
    ): array {
        $pathPrefix = $options['keyprefix'] ?? '';
        if (! is_array($storage)) {
            $storage = self::getSingle($storage);
        } else {
            foreach (self::requiredByApi((int) $storage['api_id']) as $k) {
                if (! isset($storage[$k])) {
                    throw new Exception('Missing ' . $k . ' value', 600);
                }
            }
        }
        if (! isset($storage['api_type'])) {
            $storage['api_type'] = StorageApis::getApiType((int) $storage['api_id']);
        }
        $API = self::requireAPI($storage);
        $files = [];
        if (! empty($targets['file'])) {
            $files[] = $targets;
        } elseif (! is_array($targets)) {
            $files = [
                'file' => $targets,
                'filename' => basename($targets),
            ];
        } else {
            $files = $targets;
        }
        $disk_space_used = 0;
        $cache_control = 'public, max-age=31536000';
        $urn = '';
        foreach ($files as $k => $v) {
            $source_file = $v['file'];
            switch ($storage['api_type']) {
                case 'local':
                    $target_path = $API instanceof LocalStorage
                        ? $API->realPath()
                        : $storage['bucket'];
                    $target_path .= $pathPrefix;
                    if ($pathPrefix !== '') {
                        $API->mkdirRecursive($pathPrefix);
                    }
                    $API->put([
                        'filename' => $v['filename'],
                        'source_file' => $source_file,
                        'path' => $target_path,
                    ]);
                    if (! $API instanceof LocalStorage) {
                        $API->chdir($storage['bucket']);
                    }

                    break;
                default:
                    throw new LogicException('Unsupported storage API', 600);
            }

            $filesize = @filesize($v['file']);
            if ($filesize === false) {
                throw new Exception("Can't get filesize for " . $v['file'], 601);
            }
            $disk_space_used += $filesize;

            $files[$k]['stored_file'] = $storage['url'] . $pathPrefix . $v['filename'];
        }
        if (isset($storage['id']) && $storage['id'] !== 0) {
            Variable::set('last_used_storage', (int) $storage['id']);
            DB::increment(
                'storages',
                [
                    'space_used' => '+' . $disk_space_used,
                ],
                [
                    'id' => $storage['id'],
                ]
            );
        }

        return $files;
    }

    /**
     * Delete files from the external storage.
     *
     * @param string|array $targets (key, single array key, multiple array key)
     * @param int|array $storage (storage id, storage array)
     */
    public static function deleteFiles(
        string|array $targets,
        int|array $storage,
        bool $useQueue = true
    ): array|false {
        if (! is_array($storage)) {
            $storage = self::getSingle($storage);
        } else {
            foreach (self::requiredByApi((int) $storage['api_id']) as $k) {
                if (! isset($storage[$k])) {
                    throw new Exception('Missing ' . $k . ' value', 600);
                }
            }
        }
        /** @var array $storage */
        $files = [];
        if (! empty($targets['key'])) {
            $files[] = $targets;
        } elseif (! is_array($targets)) {
            $files = [[
                'key' => $targets,
            ]];
        } else {
            $files = $targets;
        }
        $storage_keys = [];
        foreach ($files as $k => $v) {
            $files[$v['key']] = $v;
            $storage_keys[] = $v['key'];
            unset($files[$k]);
        }
        $deleted = [];
        if (isset($storage['id']) && $useQueue) {
            $storage_keysCount = count($storage_keys);
            for ($i = 0; $i < $storage_keysCount; ++$i) {
                $queue_args = [
                    'key' => $storage_keys[$i],
                    'size' => $storage['id'] !== 0
                        ? $files[$storage_keys[$i]]['size']
                        : 0,
                ];
                Queue::insert([
                    'type' => 'storage-delete',
                    'args' => json_encode($queue_args),
                    'join' => $storage['id'] ?? 0,
                ]);
                $deleted[] = $storage_keys[$i];
            }
        } else {
            foreach ($storage_keys as $key) {
                self::deleteObject($key, $storage);
                $deleted[] = $key;
            }
        }

        return $deleted !== []
            ? $deleted
            : false;
    }

    /**
     * Delete a single file from the external storage.
     *
     * @param string $key representation of the object (file) to delete relative to the bucket
     */
    public static function deleteObject(string $key, array $storage): void
    {
        $API = self::requireAPI($storage);
        switch (StorageApis::getApiType((int) $storage['api_id'])) {
            case 'local':
                $API->delete($key);

                break;
            default:
                throw new LogicException('Unsupported storage API', 600);
        }
    }

    public static function test(array|int $storage): void
    {
        $datetime = preg_replace(
            '/(.*)_(\d{2}):(\d{2}):(\d{2})/',
            '$1_$2h$3m$4s',
            datetimegmt('Y-m-d_h:i:s')
        );
        $filename = 'Chevereto_test_' . $datetime . '.png';
        $file = PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'favicon.png';
        self::uploadFiles(
            targets: [
                'file' => $file,
                'filename' => $filename,
                'mime' => 'image/png',
            ],
            storage: $storage
        );
        self::deleteFiles(
            targets: [
                'key' => $filename,
                'size' => filesize($file),
            ],
            storage: $storage,
            useQueue: false
        );
    }

    public static function insert(array $values): int
    {
        assertMaxCount('storages');
        if ($values === []) {
            throw new Exception('Empty values provided', 600);
        }
        $required = ['name', 'api_id', 'key', 'secret', 'bucket', 'url']; // Global
        $storage_api = StorageApis::getApiType((int) $values['api_id']);
        if ($storage_api === 'local' && ! (bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) {
            throw new Exception('Local storage API is forbidden', 403);
        }
        if ($storage_api === 'local') {
            unset($required[2], $required[3]); //  key, secret
        }
        foreach ($required as $v) {
            if (! check_value($values[$v])) {
                throw new Exception("Missing {$v} value", 101);
            }
        }
        $validations = [
            'api_id' => [
                'validate' => is_numeric($values['api_id']),
                'message' => 'Expecting integer value for api_id, ' . gettype($values['api_id']) . ' given',
                'code' => 602,
            ],
            'url' => [
                'validate' => is_url($values['url']),
                'message' => 'Invalid storage URL given',
                'code' => 103,
            ],
        ];
        foreach ($validations as $k => $v) {
            if (! $v['validate']) {
                throw new Exception($v['message'], $v['code']);
            }
        }
        $values['url'] = add_ending_slash($values['url']);
        self::formatValues($values);
        self::test($values);
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_NAMES, $values);
        }

        $return = DB::insert('storages', $values);
        if (((int) $return) !== 0) {
            static::updateStorageVariables();
        }

        return $return;
    }

    public static function update(int $id, array $values, bool $checkCredentials = true): int
    {
        $storage = self::getSingle($id);
        if ($storage === []) {
            throw new Exception("Storage ID:{$id} doesn't exists", 100);
        }
        if (isset($values['url'])) {
            if (! is_url($values['url'])) {
                if (! $storage['url']) {
                    throw new Exception('Missing storage URL', 100);
                }
                unset($values['url']);
            } else {
                $values['url'] = add_ending_slash($values['url']);
            }
        }
        self::formatValues($values, 'null');
        if (isset($values['capacity']) && ! empty($values['capacity']) && $values['capacity'] < $storage['space_used']) {
            throw new Exception(_s("Storage capacity can't be lower than its current usage (%s).", format_bytes($storage['space_used'])), 101);
        }
        $new_values = array_merge($storage, $values);
        if ($checkCredentials) {
            $isTestCredentials = intval($values['is_active'] ?? 0) === 1;
            if (! $isTestCredentials) {
                foreach (['api_id', 'key', 'secret', 'bucket', 'region', 'server', 'account_id', 'account_name'] as $v) {
                    if (isset($values[$v]) && $values[$v] !== $storage[$v]) {
                        if ($v === 'api_id') {
                            unset($new_values['api_name'], $new_values['api_type']);
                        }
                        $isTestCredentials = true;

                        break;
                    }
                }
            }
            if ($isTestCredentials) {
                self::test($new_values);
            }
        }
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_NAMES, $values);
        }

        $return = DB::update('storages', $values, [
            'id' => $id,
        ]);
        if (((int) $return) !== 0) {
            static::updateStorageVariables();
        }

        return $return;
    }

    public static function requireAPI(array $storage): object
    {
        $api_type = StorageApis::getApiType((int) $storage['api_id']);
        switch ($api_type) {
            case 'local':
                return new LocalStorage($storage);
            default:
                throw new LogicException('Unsupported storage API', 600);
        }

        throw new LogicException();
    }

    public static function getAPIRegions(string $api): array
    {
        $regions = [
            's3' => [
                'us-east-1' => 'US East (N. Virginia)',
                'us-east-2' => 'US East (Ohio)',
                'us-west-1' => 'US West (N. California)',
                'us-west-2' => 'US West (Oregon)',

                'ca-central-1' => 'Canada (Central)',

                'ap-south-1' => 'Asia Pacific (Mumbai)',
                'ap-northeast-2' => 'Asia Pacific (Seoul)',
                'ap-southeast-1' => 'Asia Pacific (Singapore)',
                'ap-southeast-2' => 'Asia Pacific (Sydney)',
                'ap-northeast-1' => 'Asia Pacific (Tokyo)',

                'eu-central-1' => 'EU (Frankfurt)',
                'eu-west-1' => 'EU (Ireland)',
                'eu-west-2' => 'EU (London)',
                'eu-west-3' => 'EU (Paris)',

                'sa-east-1' => 'South America (Sao Paulo)',
            ],
        ];
        foreach ($regions['s3'] as $k => &$v) {
            $v = [
                'name' => $v,
                'url' => '',
            ];
        }

        return $regions[$api];
    }

    public static function getStorageValidFilename(
        string $filename,
        int $storage_id,
        string $filenaming,
        string $destination
    ): string {
        if ($filenaming === 'id') {
            return $filename;
        }
        $extension = get_file_extension($filename);
        $wanted_names = [];
        for ($i = 0; $i < 25; ++$i) {
            if ($i > 0 && $i < 5) {
                $filenaming = $filenaming === 'random' ? 'random' : 'mixed';
            } elseif ($i > 15) {
                $filenaming = 'random';
            }
            $filename_by_method = get_filename_by_method($filenaming, $filename);
            $wanted_names[] = get_basename_without_extension($filename_by_method);
        }
        $taken_names = [];
        if ($storage_id !== 0) {
            $stock_qry = 'SELECT DISTINCT image_name, image_id FROM ' . DB::getTable('images') . ' WHERE image_storage_id=:image_storage_id AND image_extension=:image_extension AND image_name IN(' . '"' . implode('","', $wanted_names) . '"' . ') ';
            $stock_binds = [
                'storage_id' => $storage_id,
                'extension' => $extension,
            ];
            $datefolder = rtrim(preg_replace('#' . CHV_PATH_IMAGES . '#', '', $destination, 1), '/');
            if (preg_match('#\d{4}\/\d{2}\/\d{2}#', $datefolder)) {
                $datefolder = str_replace('/', '-', $datefolder);
                $stock_qry .= 'AND DATE(image_date)=:image_date ';
                $stock_binds['date'] = $datefolder;
            }
            $stock_qry .= 'ORDER BY image_id DESC;';

            try {
                $db = DB::getInstance();
                $db->query($stock_qry);
                foreach ($stock_binds as $k => $v) {
                    $db->bind(':image_' . $k, $v);
                }
                $images_stock = $db->fetchAll();
                foreach ($images_stock as $k => $v) {
                    $taken_names[] = $v['image_name'];
                }
            } catch (Exception) {
            }
        }
        if ($taken_names !== []) {
            foreach ($wanted_names as $candidate) {
                if (in_array($candidate, $taken_names, true)) {
                    continue;
                }
                $return = $candidate;

                break;
            }
        } else {
            $return = $wanted_names[0];
        }

        return isset($return)
            ? ($return . '.' . $extension)
            : self::getStorageValidFilename($filename, $storage_id, $filenaming, $destination);
    }

    public static function regenStorageStats(int $storageId): string
    {
        $storage = self::getSingle($storageId);
        if ($storage === []) {
            throw new Exception(sprintf("Error: Storage id %s doesn't exists", $storageId), 100);
        }
        $query = 'UPDATE ' . DB::getTable('storages') . ' SET storage_space_used = (SELECT IFNULL(SUM(image_size) + SUM(image_thumb_size) + SUM(image_medium_size),0) FROM ' . DB::getTable('images') . ' WHERE image_storage_id = :storageId) WHERE storage_id = :storageId';
        $db = DB::getInstance();
        $db->query($query);
        if ($storageId !== 0) {
            $db->bind(':storageId', $storageId);
        }
        $db->exec();

        return sprintf(
            'Storage %s stats re-generated',
            $storageId != 0
                ? ('"' . $storage['name'] . '" (' . $storage['id'] . ')')
                : 'local'
        );
    }

    public static function migrateStorage(int $sourceStorageId, int $targetStorageId): string
    {
        if ($sourceStorageId === $targetStorageId) {
            throw new Exception(sprintf('You have to provide two different storage ids (same id %s provided)', $sourceStorageId), 100);
        }
        $sourceStorage = $sourceStorageId === 0
            ? 'local'
            : self::getSingle($sourceStorageId);
        $targetStorage = $targetStorageId === 0
            ? 'local'
            : self::getSingle($targetStorageId);
        $error_message = ["Storage id %s doesn't exists", "Storage ids %s doesn't exists"];
        $error = [];
        foreach (['source', 'target'] as $v) {
            $prop = $v . 'Storage';
            $id = $prop . 'Id';
            if (${$prop} == false) {
                $error[] = ${$id};
            } elseif (is_array(${$prop}) === false) {
                ${$prop} = [
                    'name' => ${$prop},
                    'type' => ${$prop},
                    'api_type' => ${$prop},
                ];
            }
        }
        if ($error !== []) {
            throw new Exception(str_replace('%s', implode(', ', $error), $error_message[count($error) - 1]));
        }
        $db = DB::getInstance();
        $query = 'UPDATE ' . DB::getTable('images') . ' SET image_storage_id = :targetStorageId WHERE ';
        // local (null) -> external
        if ($sourceStorageId === 0) {
            $query .= 'ISNULL(image_storage_id)';
            // external -> external
        } else {
            $query .= 'image_storage_id = :sourceStorageId';
        }
        $db->query($query);
        if ($sourceStorageId !== 0) {
            $db->bind(':sourceStorageId', $sourceStorageId);
        }
        $db->bind(':targetStorageId', $targetStorageId === 0 ? null : $targetStorageId);
        $db->exec();
        $rowCount = $db->rowCount();
        if ($rowCount > 0) {
            $return = [];
            if ($sourceStorageId !== 0) {
                $return[] = static::regenStorageStats($sourceStorageId);
            }
            if ($targetStorageId !== 0) {
                $return[] = static::regenStorageStats($targetStorageId);
            }
            array_unshift($return, strtr('OK: %s image(s) migrated from "%source" to "%target"', [
                '%s' => $rowCount,
                '%source' => $sourceStorage['name'],
                '%target' => $targetStorage['name'],
            ]));

            return implode(' - ', $return);
        }

        throw new Exception('No content to migrate', 404);
    }

    public static function deleteAllFiles(int $id): int
    {
        $ids = DB::get(
            table: 'images',
            where: [
                'storage_id' => $id,
            ],
            fetch_style: PDO::FETCH_COLUMN
        );

        return Image::deleteMultiple($ids);
    }

    public static function delete(int $id): int
    {
        static::deleteAllFiles($id);

        $return = DB::update(
            'storages',
            [
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s', time()),
            ],
            [
                'id' => $id,
            ]
        );
        if (((int) $return) !== 0) {
            static::updateStorageVariables();
        }

        return $return;
    }

    public static function getThrowableMessage(Throwable $throwable): string
    {
        return $throwable->getMessage();
    }

    protected static function updateStorageVariables(): void
    {
        $table = DB::getTable('storages');
        $activeSQL = <<<MySQL
        SELECT COUNT(*) count FROM `{$table}` WHERE storage_is_active=1 AND storage_deleted_at IS NULL;
        MySQL;
        $allSQL = <<<MySQL
        SELECT COUNT(*) count FROM `{$table}` WHERE storage_deleted_at IS NULL;
        MySQL;
        $activeStorage = (int) (DB::queryFetchSingle($activeSQL)['count'] ?? 0);
        $allStorage = (int) (DB::queryFetchSingle($allSQL)['count'] ?? 0);
        Variable::set('storages_active', $activeStorage);
        Variable::set('storages_all', $allStorage);
    }

    protected static function requiredByApi(int $api_id): array
    {
        $required = ['api_id', 'bucket'];
        $type = StorageApis::getApiType($api_id);
        if ($type !== 'local') {
            $required[] = 'secret';
            if ($type !== 'gcloud') {
                $required[] = 'key';
            }
        }

        return $required;
    }

    protected static function formatValues(array &$values, string $junk = 'keep'): void
    {
        if (isset($values['capacity'])) {
            nullify_string($values['capacity']);
            if ($values['capacity'] != null) {
                $values['capacity'] = get_bytes($values['capacity']);
                if (! is_numeric($values['capacity'])) {
                    throw new Exception('Invalid storage capacity value. Make sure to use a valid format.', 100);
                }
            }
        }
        if (isset($values['is_https'])) {
            $protocol_stock = ['http', 'https'];
            if ($values['is_https'] != 1) {
                $protocol_stock = array_reverse($protocol_stock);
            }
            $values['url'] = preg_replace('#^https?://#', '', $values['url'], 1);
            $values['url'] = $protocol_stock[1] . '://' . $values['url'];
        } elseif (isset($values['url'])) {
            $values['is_https'] = (int) is_https($values['url']);
        }

        if (in_array($junk, ['null', 'remove'], true)
            && isset($values['api_id'])
        ) {
            $junk_values_by_api = [
                1 => ['server'],
                5 => ['region'],
            ];
            if (isset($junk_values_by_api[$values['api_id']])) {
                switch ($junk) {
                    case 'null':
                        foreach ($junk_values_by_api[$values['api_id']] as $v) {
                            $values[$v] = null;
                        }

                        break;
                    case 'remove':
                        $values = array_filter_array($values, $junk_values_by_api[$values['api_id']], 'rest');

                        break;
                }
            }
        }
    }

    protected static function formatRowValues(array &$values, array $row = []): void
    {
        $values = DB::formatRow($row !== [] ? $row : $values);
        $values['name'] = safe_html($values['name']);
        $values['url'] = is_url($values['url'])
            ? add_ending_slash($values['url'])
            : null;
        $capacity = format_bytes($values['capacity'], 0);
        $used = format_bytes($values['space_used'], 2);
        $values['usage_label'] = ($values['capacity'] === 0 ? _s('Unlimited') : $capacity)
            . ' / '
            . $used
            . ' '
            . _s('used');
        if (hasEncryption()) {
            $values = decryptValues(self::ENCRYPTED_NAMES, $values);
        }
    }
}
