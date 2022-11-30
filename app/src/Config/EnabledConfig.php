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

final class EnabledConfig
{
    public function __construct(
        private bool $phpPages = false,
        private bool $updateCli = true,
        private bool $updateHttp = true,
        private bool $htaccessCheck = true,
    ) {
    }

    public function phpPages(): bool
    {
        return $this->phpPages;
    }

    public function updateCli(): bool
    {
        return $this->updateCli;
    }

    public function updateHttp(): bool
    {
        return $this->updateHttp;
    }

    public function htaccessCheck(): bool
    {
        return $this->htaccessCheck;
    }
}
