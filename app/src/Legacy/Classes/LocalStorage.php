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

use function Chevereto\Legacy\G\is_writable;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\unlinkIfExists;
use Exception;

class LocalStorage
{
    private string $path;

    private string $realPath;

    private array $deleted = [];

    public function __construct(array $args = [])
    {
        $this->path = rtrim($args['bucket'], '/') . '/';
        $this->realPath = realpath($this->path) . '/';
        if ($this->realPath === '/') {
            $this->realPath = $this->path;
        }
        $this->assertPath($this->realPath);
    }

    public function realPath(): string
    {
        return $this->realPath;
    }

    protected function assertPath(string $path): void
    {
        if (is_writable($path) === false) {
            throw new Exception(
                sprintf("Path %s is not writable", $path),
                600
            );
        }
    }

    public function put(array $args = []): void
    {
        // [filename] => photo-1460378150801-e2c95cb65a50.jpg
        // [source_file] => /tmp/photo-1460378150801-e2c95cb65a50.jpg
        // [path] => /path/sdk/2018/08/18/
        extract($args);
        $path ??= '';
        $filename ??= '';
        $source_file ??= '';
        $this->assertPath($path);
        $target_filename = $path . $filename;
        $target_filename = str_replace('/.\/', '/', $target_filename);
        if ($source_file == $target_filename) {
            return;
        }
        $uploaded = copy($source_file, $target_filename);
        $errors = error_get_last();
        if ($uploaded == false) {
            throw new Exception(
                strtr("Can't move source file %source% to %destination%: %message%", [
                    '%source%' => $source_file,
                    '%destination%' => $target_filename,
                    '%message%' => 'Copy error ' . $errors['type'] . ' > ' . $errors['message'],
                ]),
                600
            );
        }
        chmod($target_filename, 0644);
        clearstatcache();
    }

    public function delete(string $filename): void
    {
        $filename = $this->getWorkingPath($filename);
        if (file_exists($filename) == false) {
            return;
        }
        if (unlinkIfExists($filename) == false) {
            throw new Exception("Can't delete file '$filename'", 600);
        }
        clearstatcache();
    }

    public function deleteMultiple(array $filenames = []): void
    {
        $this->deleted = [];
        foreach ($filenames as $v) {
            $this->delete($v);
            $this->deleted[] = $v;
        }
    }

    public function deleted(): array
    {
        return $this->deleted;
    }

    public function mkdirRecursive(string $dirname): void
    {
        $dirname = $this->getWorkingPath($dirname);
        if (is_dir($dirname)) {
            return;
        }
        $path_perms = fileperms($this->realPath);
        $old_umask = umask(0);
        $make_pathname = mkdir($dirname, $path_perms, true);
        chmod($dirname, $path_perms);
        umask($old_umask);
        if (!$make_pathname) {
            throw new Exception('$dirname ' . $dirname . ' is not a dir', 630);
        }
    }

    protected function getWorkingPath(string $dirname): string
    {
        if (starts_with('/', $dirname) == false) { // relative thing
            return $this->realPath . $dirname;
        }

        return realpath($dirname);
    }
}
