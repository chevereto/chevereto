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
use Throwable;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\G\array_filter_array;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\isSafeToExecute;

class Queue
{
    public static ?int $max_execution_time;

    public static function insert($values): void
    {
        $values = array_merge([
            'date_gmt' => datetimegmt(),
            'status' => 'pending',
        ], $values);
        DB::insert('queues', $values);
    }

    public static function process(array $args): void
    {
        self::$max_execution_time = (int) ini_get('max_execution_time');
        $queues_db = DB::get(
            [
                'table' => 'queues',
                'join' => 'LEFT JOIN '
                    . DB::getTable('storages')
                    . ' ON '
                    . DB::getTable('queues')
                    . '.queue_join = '
                    . DB::getTable('storages')
                    . '.storage_id',
            ],
            [
                'type' => $args['type'],
                'status' => 'pending',
            ],
            'AND',
            [],
            250
        );
        $queues = [];
        foreach ($queues_db as $k => $v) {
            $queue_item = DB::formatRow($v);
            $queue_item['args'] = json_decode($queue_item['args'], true);
            if (! array_key_exists($queue_item['storage']['id'], $queues)) {
                $queues[$queue_item['storage']['id']] = [
                    'storage' => $queue_item['storage'],
                    'files' => [],
                ];
            }
            $queues[$queue_item['storage']['id']]['files'][] = array_filter_array($queue_item, ['id', 'args'], 'exclusion');
        }
        $assetStorage = AssetStorage::getDbSettings();
        $assetStorage['id'] = 0;
        foreach ($queues as $k => $storage_queue) {
            if (! self::canKeepGoing()) {
                break;
            }
            $storage = $storage_queue['storage'];
            if ($storage['id'] === null) { // asset
                $storage = $assetStorage;
            }
            $storage_files = $storage_queue['files'];
            $storage['api_type'] = StorageApis::getApiType((int) $storage['api_id']);
            if (hasEncryption() && $storage['id'] !== 0) {
                $storage = decryptValues(Storage::ENCRYPTED_NAMES, $storage);
            }
            $files = [];
            $storage_keys = [];
            $deleted_queue_ids = [];
            $disk_space_freed = 0;
            $disk_space_used = 0;
            foreach ($storage_files as $k => $v) {
                $files[$v['args']['key']] = array_merge($v['args'], [
                    'id' => $v['id'],
                ]);
                switch ($storage['api_type']) {
                    case 'local':
                        $storage_keys[] = $v['args']['key'];

                        break;
                    default:
                        throw new Exception('Unsupported storage API type: ' . $storage['api_type']);
                }
                unset($files[$k]);
                $disk_space_used += $v['args']['size'];
                $deleted_queue_ids[] = $v['id']; // Generate the queue_id stock
            }

            try {
                $StorageAPI = Storage::requireAPI($storage);
            } catch (Throwable $e) {
                self::logAttempt($deleted_queue_ids);
                $error = $e;

                break;
            }
            switch ($storage['api_type']) {
                case 'local':
                    $StorageAPI->deleteMultiple($storage_keys);
                    $deleted_queue_ids = []; // All over again
                    foreach ($StorageAPI->deleted() as $k => $v) {
                        $disk_space_freed += $files[$v]['size'];
                        $deleted_queue_ids[] = $files[$v]['id'];
                    }

                    break;
                default:
                    throw new Exception('Unsupported storage API type: ' . $storage['api_type']);
            }
            self::logAttempt($deleted_queue_ids);
            if (isset($error) && $error instanceof Exception) {
                throw $error;
            }
            if ($storage['id'] !== 0) {
                DB::increment(
                    'storages',
                    [
                        'space_used' => '-' . $disk_space_freed,
                    ],
                    [
                        'id' => $storage['id'],
                    ]
                );
            }
            self::delete($deleted_queue_ids);
        }
    }

    public static function delete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }
        $db = DB::getInstance();
        $db->query('DELETE from ' . DB::getTable('queues') . ' WHERE queue_id IN (' . implode(',', $ids) . ')');

        return $db->exec() ? $db->rowCount() : 0;
    }

    public static function logAttempt(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $db = DB::getInstance();
        $db->query('UPDATE ' . DB::getTable('queues') . ' SET queue_attempts = queue_attempts + 1, queue_status = IF(queue_attempts > 3, "failed", "pending") WHERE queue_id IN (' . implode(',', $ids) . ')');
        $db->exec();
    }

    public static function canKeepGoing(): bool
    {
        return isSafeToExecute(self::$max_execution_time);
    }
}
