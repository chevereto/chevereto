<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Storage;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class Storage implements StorageInterface
{
    public function __construct(private string $location)
    {
    }

    final public function adapter(): FilesystemAdapter
    {
        return new LocalFilesystemAdapter($this->location);
    }
}
