<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevereto;

use Chevereto\Legacy\Classes\Settings;

/**
 * Perl Compatible Regular Expressions.
 */
enum PCRE: string
{
    case USER_USERNAME = '/^' . Settings::USERNAME_PATTERN . '$/';

    case USER_EMAIL = '/^.+\@{1}.+$/';

    case USER_PASSWORD = '/^' . Settings::USER_PASSWORD_PATTERN . '$/';
}
