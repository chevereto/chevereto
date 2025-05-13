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

use Chevereto\Legacy\Classes\Traits\BinaryTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ExifTool
{
    use BinaryTrait;

    public function name(): string
    {
        return 'ExifTool';
    }

    public function version(): string
    {
        $process = new Process([$this->path, '-ver']);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    public function strip(string $path): void
    {
        $process = new Process([
            $this->path,
            '-all=',
            '-overwrite_original',
            $path,
        ]);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
