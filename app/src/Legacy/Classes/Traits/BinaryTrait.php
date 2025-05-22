<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes\Traits;

use RuntimeException;

trait BinaryTrait
{
    private string $path;

    private array $allowedDirs = [];

    private array $pathDirs = [];

    /**
     * @param string $binary The binary file (shortname or full path)
     */
    public function __construct(
        string $binary,
        private ?string $name = null
    ) {
        $this->name ??= $name;
        $openBasedir = ini_get('open_basedir');
        if ($openBasedir) {
            $this->allowedDirs = array_filter(explode(PATH_SEPARATOR, $openBasedir));
            $this->trailingSlashArray($this->allowedDirs);
        }
        $envPath = getenv('PATH') ?: getenv('Path') ?: '';
        $this->pathDirs = array_filter(explode(PATH_SEPARATOR, $envPath));
        $this->trailingSlashArray($this->pathDirs);
        if ($this->allowedDirs !== []) {
            $this->pathDirs = array_values(
                array_intersect($this->pathDirs, $this->allowedDirs)
            );
        }
        $this->path = $this->finder($binary);
        if (! is_executable($this->path)) {
            throw new RuntimeException("{$this->name()} binary not executable", 3);
        }
    }

    abstract public function name(): string;

    public function path(): string
    {
        return $this->path;
    }

    private function finder(string $binary): string
    {
        if ($binary === '') {
            throw new RuntimeException("{$this->name()} binary not provided", 1);
        }

        if (strpbrk($binary, '/\\') !== false) {
            if ($this->allowedDirs !== []) {
                $dirname = $this->trailingSlash(dirname($binary));
                if (in_array($dirname, $this->allowedDirs) && is_file($binary)) {
                    return $binary;
                }
            } elseif (is_file($binary)) {
                return $binary;
            }

            throw new RuntimeException("{$this->name()} binary not found at specified path", 2);
        }

        foreach ($this->pathDirs as $dir) {
            $candidate = $dir . $binary;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("{$this->name()} binary not found at PATH", 2);
    }

    private function trailingSlashArray(array &$dirs): void
    {
        foreach ($dirs as &$dir) {
            $dir = $this->trailingSlash($dir);
        }
        unset($dir);
    }

    private function trailingSlash(string $path): string
    {
        return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }
}
