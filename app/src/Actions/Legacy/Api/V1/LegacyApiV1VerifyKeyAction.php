<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Actions\Legacy\Api\V1;

use Chevere\Action\Action;
use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\InvalidArgumentException;

class LegacyApiV1VerifyKeyAction extends Action
{
    public function run(
        string $key,
        string $apiV1Key
    ): array {
        if ($key !== $apiV1Key) {
            throw new InvalidArgumentException(
                message: message('Invalid API V1 key provided'),
                code: 100
            );
        }

        return [];
    }
}
