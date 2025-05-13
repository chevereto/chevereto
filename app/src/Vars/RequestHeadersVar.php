<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Vars;

use Chevereto\Vars\Traits\ImmutableMapTrait;

final class RequestHeadersVar
{
    use ImmutableMapTrait;

    public function __construct(array $array)
    {
        $this->assertNoInstance();
        $headers = [];
        foreach ($array as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', ucwords(strtolower(substr($key, 5)), '_'));
                $headers[$header] = $value;
            }
        }
        static::$array = $headers;
    }
}
