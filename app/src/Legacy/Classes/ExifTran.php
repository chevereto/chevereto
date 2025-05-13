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

final class ExifTran
{
    use BinaryTrait;

    public function name(): string
    {
        return 'ExifTran';
    }

    public function orientate(string $path): void
    {
        $process = new Process([
            $this->path,
            '-a',
            '-i',
            $path,
        ]);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
