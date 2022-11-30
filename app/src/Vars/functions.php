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

use Chevere\Throwable\Exceptions\LogicException;
use Ds\Map;

function env(): array
{
    try {
        return EnvVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function request(): array
{
    try {
        return RequestVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function get(): array
{
    try {
        return GetVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function post(): array
{
    try {
        return PostVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function server(): array
{
    try {
        return ServerVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function files(): array
{
    try {
        return FilesVar::toArray();
    } catch (LogicException $e) {
        return [];
    }
}

function cookie(): array
{
    try {
        return cookieVar()->toArray();
    } catch (LogicException $e) {
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
    } catch (LogicException $e) {
        return [];
    }
}

function sessionVar(): Map
{
    return SessionVar::map();
}
