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

use Chevere\DataStructure\Interfaces\MapMutableInterface;
use LogicException;

function env(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = EnvVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function request(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = RequestVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function get(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = GetVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function post(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = PostVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function server(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = ServerVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function files(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = FilesVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}

function cookie(): array
{
    try {
        return cookieVar()->toArray();
    } catch (LogicException) {
        return [];
    }
}

function cookieVar(): MapMutableInterface
{
    return CookieVar::map();
}

function session(): array
{
    try {
        return sessionVar()->toArray();
    } catch (LogicException) {
        return [];
    }
}

function sessionVar(): MapMutableInterface
{
    return SessionVar::map();
}

function requestHeaders(): array
{
    static $cache;
    if (! isset($cache)) {
        try {
            $cache = RequestHeadersVar::toArray();
        } catch (LogicException) {
            $cache = [];
        }
    }

    return $cache;
}
