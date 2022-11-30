<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\L10n;

function _s(string $msg, $args = null)
{
    $msg = L10n::gettext($msg);
    if ($msg && !is_null($args)) {
        $fn = is_array($args) ? 'strtr' : 'sprintf';
        $msg = $fn($msg, $args);
    }

    return $msg;
}

function _se(string $msg, $args = null)
{
    echo _s($msg, $args);
}

function _n(string $msg, string $msg_plural, string|int $count)
{
    return L10n::ngettext($msg, $msg_plural, (int) $count);
}

function _ne(string $msg, string $msg_plural, string|int $count)
{
    echo _n($msg, $msg_plural, (int) $count);
}
