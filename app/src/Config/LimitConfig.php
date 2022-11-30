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

final class LimitConfig
{
    public function __construct(
        private int $invalidRequestsPerDay = 25,
    ) {
    }

    public function invalidRequestsPerDay(): int
    {
        return $this->invalidRequestsPerDay;
    }
}
