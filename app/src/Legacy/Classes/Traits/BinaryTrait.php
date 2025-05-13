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
use Symfony\Component\Process\ExecutableFinder;

trait BinaryTrait
{
    private string $path;

    public function __construct(
        string $binary
    ) {
        $name = $this->name();
        if ($binary === '') {
            throw new RuntimeException("{$name} binary not provided", 1);
        }
        $finder = new ExecutableFinder();
        $binary = $finder->find($binary);
        if ($binary === null) {
            throw new RuntimeException("{$name} binary not found", 2);
        }
        if (! is_executable($binary)) {
            throw new RuntimeException("{$name} binary is not executable", 3);
        }
        $this->path = $binary;
    }

    abstract public function name(): string;

    public function path(): string
    {
        return $this->path;
    }
}
