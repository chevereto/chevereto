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

use function Chevereto\Legacy\G\get_basename_without_extension;
use function Chevereto\Legacy\G\get_file_extension;
use function Chevereto\Legacy\G\get_filename_by_method;
use Exception;
use LogicException;

class Storage
{
    public const ENCRYPTED_NAMES = [];

    public static function getSingle(int $var): array
    {
        return [];
    }

    public static function get(array $values = [], array $sort = [], int $limit = null): array
    {
        return [];
    }

    protected static function requiredByApi(int $api_id): array
    {
        return ['api_id', 'bucket'];
    }

    public static function uploadFiles(
        array|string $targets,
        array|int $storage,
        array $options = []
    ): array {
        $pathPrefix = $options['keyprefix'] ?? '';
        if (!is_array($storage)) {
            throw new LogicException('Invalid storage');
        } else {
            foreach (self::requiredByApi((int) $storage['api_id']) as $k) {
                if (!isset($storage[$k])) {
                    throw new Exception('Missing ' . $k . ' value', 600);
                }
            }
        }
        if (!isset($storage['api_type'])) {
            $storage['api_type'] = 'local';
        }
        $API = self::requireAPI($storage);
        $files = [];
        if (!empty($targets['file'])) {
            $files[] = $targets;
        } elseif (!is_array($targets)) {
            $files = ['file' => $targets, 'filename' => basename($targets)];
        } else {
            $files = $targets;
        }
        $disk_space_used = 0;
        foreach ($files as $k => $v) {
            $source_file = $v['file'];
            $target_path = $API->realPath() . $pathPrefix;
            if ($pathPrefix !== '') {
                $API->mkdirRecursive($pathPrefix);
            }
            $API->put([
                'filename' => $v['filename'],
                'source_file' => $source_file,
                'path' => $target_path,
            ]);
            $filesize = @filesize($v['file']);
            if ($filesize === false) {
                throw new Exception("Can't get filesize for " . $v['file'], 601);
            } else {
                $disk_space_used += $filesize;
            }
            $files[$k]['stored_file'] = $storage['url'] . $pathPrefix . $v['filename'];
        }

        return $files;
    }

    /**
     * Delete files from the external storage (using queues for non anon Storages).
     *
     * @param string|array $targets (key, single array key, multiple array key)
     * @param int|array $storage (storage id, storage array)
     */
    public static function deleteFiles(string|array $targets, int|array $storage): array|bool
    {
        if (!is_array($storage)) {
            throw new LogicException('Invalid storage');
        } else {
            foreach (self::requiredByApi((int) $storage['api_id']) as $k) {
                if (!isset($storage[$k])) {
                    throw new Exception('Missing ' . $k . ' value', 600);
                }
            }
        }
        /** @var array $storage */
        $files = [];
        if (!empty($targets['key'])) {
            $files[] = $targets;
        } elseif (!is_array($targets)) {
            $files = [['key' => $targets]];
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
        foreach ($storage_keys as $key) {
            self::deleteObject($key, $storage);
            $deleted[] = $key;
        }

        return $deleted !== [] ? $deleted : false;
    }

    /**
     * Delete a single file from the external storage.
     *
     * @param string $key representation of the object (file) to delete relative to the bucket
     */
    public static function deleteObject(string $key, array $storage): void
    {
        self::requireAPI($storage)->delete($key);
    }

    public static function test(array|int $storage): void
    {
    }

    public static function insert(array $values): int
    {
        return 0;
    }

    public static function update(int $id, array $values, bool $checkCredentials = true): int
    {
        return 0;
    }

    public static function requireAPI(array $storage): object
    {
        return new LocalStorage($storage);
    }

    public static function getAPIRegions(string $api): array
    {
        return [];
    }

    public static function getStorageValidFilename(
        string $filename,
        int $storage_id,
        string $filenaming,
        string $destination
    ): string {
        if ($filenaming == 'id') {
            return $filename;
        }
        $extension = get_file_extension($filename);
        $wanted_names = [];
        for ($i = 0; $i < 25; ++$i) {
            if ($i > 0 && $i < 5) {
                $filenaming = $filenaming == 'random' ? 'random' : 'mixed';
            } elseif ($i > 15) {
                $filenaming = 'random';
            }
            $filename_by_method = get_filename_by_method($filenaming, $filename);
            $wanted_names[] = get_basename_without_extension($filename_by_method);
        }
        $return = $wanted_names[0];

        return isset($return) ? ($return . '.' . $extension) : self::getStorageValidFilename($filename, $storage_id, $filenaming, $destination);
    }

    public static function regenStorageStats(int $storageId): string
    {
        return '';
    }

    public static function migrateStorage(int $sourceStorageId, int $targetStorageId): string
    {
        return '';
    }
}
