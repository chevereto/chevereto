<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Auth;

use Chevere\Action\Action;
use Chevere\Parameter\Attributes\StringAttr;
use InvalidArgumentException;

final class AuthVerifyCSRFTokenAction extends Action
{
    public function run(
        #[StringAttr(description: 'Token granted to the user session.')]
        string $sessionValue,
        #[StringAttr(description: 'Token provided by the user.')]
        string $userInput
    ): array {
        if (! hash_equals($sessionValue, $userInput)) {
            throw new InvalidArgumentException(
                'Invalid CSRF token'
            );
        }

        return [];
    }
}
