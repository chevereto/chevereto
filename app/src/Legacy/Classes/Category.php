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

use InvalidArgumentException;

final class Category
{
    public const URL_KEY_PATTERN = '^[\-\w]+$';

    public const URL_KEY_PATTERN_DELIMITED = '/' . self::URL_KEY_PATTERN . '/';

    public static function assertUrlKey(string $url_key): void
    {
        if (preg_match(self::URL_KEY_PATTERN_DELIMITED, $url_key)) {
            return;
        }

        throw new InvalidArgumentException('Invalid URL key');
    }
}
