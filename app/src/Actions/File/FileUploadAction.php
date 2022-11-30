<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\File;

use Chevere\Action\Action;
use Chevere\Filesystem\Interfaces\FilenameInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use Chevereto\Storage\Storage;

/**
 * Upload the filename to the target storage.
 * @TODO If this does storage, it should be under /Storage
 */
class FileUploadAction extends Action
{
    public function run(
        string $filepath,
        FilenameInterface $targetFilename,
        Storage $storage,
        PathInterface $path
    ): array {
        return [];
    }
}
