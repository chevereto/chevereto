<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Config;

final class SystemConfig
{
    public function __construct(
        private int $debugLevel = 1,
        private string $errorLog = 'php://stderr',
        private array $imageFormatsAvailable = ['PNG', 'GIF', 'JPEG', 'BMP', 'WEBP'],
        private string $imageLibrary = 'imagick',
        private string $sessionSaveHandler = 'files',
        private string $sessionSavePath = '/tmp',
    ) {
    }

    public function debugLevel(): int
    {
        return $this->debugLevel;
    }

    public function errorLog(): string
    {
        return $this->errorLog;
    }

    public function imageFormatsAvailable(): array
    {
        return $this->imageFormatsAvailable;
    }

    public function imageLibrary(): string
    {
        return $this->imageLibrary;
    }

    public function sessionSaveHandler(): string
    {
        return $this->sessionSaveHandler;
    }

    public function sessionSavePath(): string
    {
        return $this->sessionSavePath;
    }
}
