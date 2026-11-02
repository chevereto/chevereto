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

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Composer\CaBundle\CaBundle;
use Exception;
use LogicException;
use PDO;
use Throwable;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\assertMaxCount;
use function Chevereto\Legacy\cheveretoVersionInstalled;
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
use function Chevereto\Legacy\G\str_replace_last;
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

    public static function get(array $values = [], array $sort = [], ?int $limit = null): array
    {
        $valueOperators = [
            'type_chain' => '&',
        ];
        if (version_compare(cheveretoVersionInstalled(), '4.2.0', '>=')) {
            $values['deleted_at'] = $values['deleted_at'] ?? null;
            $valueOperators['deleted_at'] = 'IS';
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
            $valueOperators
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
            $required = self::requiredByApi((int) ($storage['api_id'] ?? 0));
            foreach ($required as $k) {
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
            if (in_array($storage['api_type'], ['s3', 's3compatible'], true)) {
                switch ($storage['api_type']) {
                    case 'oss':
                        $source_file = file_get_contents($v['file']);

                        break;
                    default:
                        $source_file = @fopen($v['file'], 'r');

                        break;
                }
                if ($source_file === false) {
                    throw new Exception('Failed to open file stream', 600);
                }
                $urn = $pathPrefix . $v['filename'];
            }
            switch ($storage['api_type']) {
                case 's3':
                case 's3compatible':
                    $array = [
                        'Bucket' => $storage['bucket'],
                        'Key' => $urn,
                        'Body' => $source_file,
                        'CacheControl' => $cache_control,
                        'ContentType' => $v['mime'],
                    ];
                    if ($storage['api_type'] === 's3compatible') {
                        $array['ACL'] = 'public-read';
                    }
                    if ($storage['server'] ?? false) {
                        $host = parse_url($storage['server'], PHP_URL_HOST) ?? '';
                        if (str_ends_with($host, '.r2.cloudflarestorage.com')) {
                            unset($array['ACL']);
                        }
                    }

                    /** @var S3Client $API */
                    $API->putObject($array);

                    break;

                case 'local':
                    /** @var LocalStorage $API */
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
            case 's3':
            case 's3compatible':
                /** @var S3Client $API */
                $API->deleteObject([
                    'Bucket' => $storage['bucket'],
                    'Key' => $key,
                ]);

                break;

            case 'local':
                $API->delete($key);

                break;
        }
    }

    public static function test(array|int $storage): void
    {
        $datetime = preg_replace('/(.*)_(\d{2}):(\d{2}):(\d{2})/', '$1_$2h$3m$4s', datetimegmt('Y-m-d_h:i:s'));
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
        $required_by_api = [
            's3' => ['region', 'use_path_style_endpoint'],
            's3compatible' => ['region', 'server', 'use_path_style_endpoint'],
        ];
        $storage_api = StorageApis::getApiType((int) $values['api_id']);
        if ($storage_api === 'local' && ! (bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) {
            throw new Exception('Local storage API is forbidden', 403);
        }
        if ($storage_api === 'local') {
            unset($required[2], $required[3]); //  key, secret
        }
        if (isset($values['api_id']) && array_key_exists(StorageApis::getApiType((int) $values['api_id']), $required_by_api)) {
            foreach ($required_by_api[$storage_api] as $v) {
                $required[] = $v;
            }
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
        $chainMask = ($storage['is_image'] ? 1 : 0) | ($storage['is_video'] ? 2 : 0);
        self::formatValues($values, $chainMask, 'null');
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
        $apiId = (int) $storage['api_id'];
        $api_type = StorageApis::getApiType($apiId);
        switch ($api_type) {
            case 's3':
            case 's3compatible':
                $clientConfig = [
                    'version' => '2006-03-01',
                    'region' => $storage['region'],
                    'command.params' => [
                        'PathStyle' => true,
                    ],
                    'credentials' => [
                        'key' => $storage['key'],
                        'secret' => $storage['secret'],
                    ],
                    'http' => [
                        'verify' => CaBundle::getBundledCaBundlePath(),
                    ],
                    'use_aws_shared_config_files' => false,
                    'use_path_style_endpoint' => (bool) ($storage['use_path_style_endpoint'] ?? false),
                ];
                if ($api_type === 's3compatible') {
                    $clientConfig['endpoint'] = $storage['server'];
                    if (str_contains($storage['server'], '.r2.cloudflarestorage.com')) {
                        $clientConfig['endpoint'] = str_replace_last('/' . $storage['bucket'], '', $storage['server']);
                    }
                    $clientConfig['request_checksum_calculation'] = 'WHEN_REQUIRED';
                    $clientConfig['response_checksum_validation'] = 'WHEN_REQUIRED';
                }

                return new S3Client($clientConfig);

            case 'local':
                return new LocalStorage($storage);
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

    public static function statsRebuild(int $id): int
    {
        $storage = self::getSingle($id);
        if ($storage === []) {
            throw new Exception(sprintf("Storage id %s doesn't exists", $id), 404);
        }
        $tableStorages = DB::getTable('storages');
        $tableImages = DB::getTable('images');
        $query = <<<SQL
        UPDATE {$tableStorages}
        SET storage_space_used = (SELECT IFNULL(SUM(image_size) + SUM(image_thumb_size) + SUM(image_medium_size),0)
        FROM {$tableImages} WHERE image_storage_id = :storageId)
        WHERE storage_id = :storageId;
        SQL;
        $db = DB::getInstance();
        $db->query($query);
        $db->bind(':storageId', $id);
        $db->exec();
        $db->query(
            <<<SQL
            SELECT storage_space_used
            FROM {$tableStorages}
            WHERE storage_id = :storageId
            SQL,
        );
        $db->bind(':storageId', $id);
        $db->exec();

        return (int) $db->fetchColumn();
    }

    public static function migrateStorage(int $sourceId, int $targetId): string
    {
        if ($sourceId === $targetId) {
            throw new Exception(sprintf('You have to provide two different storage ids (same id %s provided)', $sourceId), 100);
        }
        $sourceStorage = $sourceId === 0
            ? 'local'
            : self::getSingle($sourceId);
        $targetStorage = $targetId === 0
            ? 'local'
            : self::getSingle($targetId);
        $error_message = ["Storage id %s doesn't exists", "Storage ids %s doesn't exists"];
        $error = [];
        foreach (['source', 'target'] as $v) {
            $prop = $v . 'Storage';
            $id = $v . 'Id';
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
        if ($sourceId === 0) {
            $query .= 'ISNULL(image_storage_id)';
            // external -> external
        } else {
            $query .= 'image_storage_id = :sourceStorageId';
        }
        $db->query($query);
        if ($sourceId !== 0) {
            $db->bind(':sourceStorageId', $sourceId);
        }
        $db->bind(':targetStorageId', $targetId === 0 ? null : $targetId);
        $db->exec();
        $rowCount = $db->rowCount();
        if ($rowCount > 0) {
            $return = [];
            if ($sourceId !== 0) {
                static::statsRebuild($sourceId);
                $return[] = sprintf('Storage "%s" (%s) stats re-generated', $sourceStorage['name'], $sourceId);
            }
            if ($targetId !== 0) {
                static::statsRebuild($targetId);
                $return[] = sprintf('Storage "%s" (%s) stats re-generated', $targetStorage['name'], $targetId);
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
        $message = $throwable->getMessage();
        if ($throwable instanceof AwsException
            && ($throwable->getAwsErrorMessage() ?? '') !== ''
        ) {
            $message = $throwable->getAwsErrorCode()
                . ': '
                . $throwable->getAwsErrorMessage();
        }
        if (! in_array($throwable->getCode(), [0, 999], true)) {
            $message .= ' [Code: ' . $throwable->getCode() . ']';
        }

        return $message;
    }

    protected static function updateStorageVariables(): void
    {
        $table = DB::getTable('storages');
        $activeSQL = <<<SQL
        SELECT COUNT(*) count FROM `{$table}` WHERE storage_is_active=1 AND storage_deleted_at IS NULL;
        SQL;
        $allSQL = <<<SQL
        SELECT COUNT(*) count FROM `{$table}` WHERE storage_deleted_at IS NULL;
        SQL;
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

    protected static function formatValues(array &$values, int $chainMask = 0, string $junk = 'keep'): void
    {
        $values['is_image'] ??= ($chainMask & 1) !== 0;
        $values['is_video'] ??= ($chainMask & 2) !== 0;
        $chainMask = 0;
        if ((bool) $values['is_image']) {
            $chainMask |= 1;
        }
        if ((bool) $values['is_video']) {
            $chainMask |= 2;
        }
        $values['type_chain'] = $chainMask;
        unset($values['is_image'], $values['is_video']);
        if (array_key_exists('capacity_formatted', $values)) {
            nullify_string($values['capacity_formatted']);
            $values['capacity'] = null;
            if ($values['capacity_formatted'] != null) {
                $values['capacity'] = get_bytes($values['capacity_formatted']);
                if (! is_numeric($values['capacity'])) {
                    throw new Exception('Invalid storage capacity value. Make sure to use a valid format.', 100);
                }
            }
            unset($values['capacity_formatted']);
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

        if (isset($values['api_id'])
            && in_array(StorageApis::getApiType((int) $values['api_id']), ['ftp', 'sftp'], true)
            && isset($values['bucket'])
        ) {
            $values['bucket'] = add_ending_slash($values['bucket']);
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
        if (isset($values['server'], $values['bucket'])) {
            $values['server'] = static::fixCloudFlareAPIUrl($values['server'], $values['bucket']);
        }
    }

    protected static function formatRowValues(array &$values, array $row = []): void
    {
        $values = DB::formatRow($row !== [] ? $row : $values);
        $values['name'] = safe_html($values['name']);
        $values['url'] = is_url($values['url'])
            ? add_ending_slash($values['url'])
            : null;
        $capacity = format_bytes($values['capacity'], 2);
        $used = format_bytes($values['space_used'], 2);
        $values['usage_label'] = (empty($values['capacity']) ? _s('Unlimited') : $capacity)
            . ' / '
            . $used
            . ' '
            . _s('used');
        if (hasEncryption()) {
            $values = decryptValues(self::ENCRYPTED_NAMES, $values);
        }
        $values['is_image'] = ($values['type_chain'] & 1) !== 0;
        $values['is_video'] = ($values['type_chain'] & 2) !== 0;
        $values['use_path_style_endpoint'] = (bool) $values['use_path_style_endpoint'];
        $values['is_https'] = (bool) $values['is_https'];
        $values['is_active'] = (bool) $values['is_active'];
        unset($values['type_chain']);
        $values['capacity_formatted'] = $capacity;
        $values['space_used_readable'] = $used;
        // $values['api'] = [
        //     'id' => $values['api_id'] ?? null,
        //     'name' => $values['api_name'] ?? null,
        //     'type' => $values['api_type'] ?? null,
        // ];
    }

    private static function fixCloudFlareAPIUrl(string $server, string $bucket): string
    {
        if (str_contains($server, '.r2.cloudflarestorage.com')) {
            return str_replace_last('/' . $bucket, '', $server);
        }

        return $server;
    }
}
