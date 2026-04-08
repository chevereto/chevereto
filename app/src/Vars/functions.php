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

/**
 * Returns the ENV array but limited when CHEVERETO_TRIAL='1' by
 * * `CHEVERETO_TRIAL_MAX_*` (numeric limits) and
 * * `CHEVERETO_TRIAL_ENABLE_*` (boolean flags).
 *
 * The trial variables may only be used to *decrease* the value of the
 * corresponding default. In other words, if the trial value is more
 * permissive than the default, the default value will always be returned.
 * This keeps the SaaS trial from accidentally granting greater privileges
 * than the shipped product.
 */
function envTrialAware(): array
{
    if (env()['CHEVERETO_TRIAL'] !== '1') {
        return env();
    }
    static $cache;
    if (! isset($cache)) {
        $cache = [];
        /** @var string $value */
        foreach (env() as $key => $value) {
            $defaultValue = $value;
            if (str_starts_with($key, 'CHEVERETO_MAX_')) {
                $trialMax = 'CHEVERETO_TRIAL_MAX_' . substr($key, strlen('CHEVERETO_MAX_'));
                /** @var string $trialValue */
                $trialValue = array_key_exists($trialMax, env())
                    ? env()[$trialMax]
                    : '0';
                $cache[$key] = intval($trialValue) > intval($defaultValue)
                    ? $defaultValue
                    : $trialValue;
            } elseif (str_starts_with($key, 'CHEVERETO_ENABLE_')) {
                $trialEnable = 'CHEVERETO_TRIAL_ENABLE_' . substr($key, strlen('CHEVERETO_ENABLE_'));
                /** @var string $trialValue */
                $trialValue = array_key_exists($trialEnable, env())
                    ? env()[$trialEnable]
                    : '0';
                $cache[$key] = intval($trialValue) > intval($defaultValue)
                    ? $defaultValue
                    : $trialValue;
            } else {
                $cache[$key] = $value;
            }
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
