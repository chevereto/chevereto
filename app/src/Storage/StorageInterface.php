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

interface StorageInterface
{
    public function __construct(string $location);

    public function adapter(): FilesystemAdapter;
}
