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
use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\String\AssertString;

final class AuthVerifyCSRFTokenAction extends Action
{
    public function run(
        #[ParameterAttribute(description: 'Token granted to the user session.')]
        string $sessionValue,
        #[ParameterAttribute(description: 'Token provided by the user.')]
        string $userInput
    ): array {
        (new AssertString($sessionValue))
            ->same($userInput);

        return [];
    }
}
