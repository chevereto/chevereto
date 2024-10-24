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

use Ds\Map;
use LogicException;

function env(): array
{
    try {
        return EnvVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function request(): array
{
    try {
        return RequestVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function get(): array
{
    try {
        return GetVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function post(): array
{
    try {
        return PostVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function server(): array
{
    try {
        return ServerVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function files(): array
{
    try {
        return FilesVar::toArray();
    } catch (LogicException) {
        return [];
    }
}

function cookie(): array
{
    try {
        return cookieVar()->toArray();
    } catch (LogicException) {
        return [];
    }
}

function cookieVar(): Map
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

function sessionVar(): Map
{
    return SessionVar::map();
}
