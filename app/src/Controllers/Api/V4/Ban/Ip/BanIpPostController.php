<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Ban\Ip;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;

final class BanIpPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Creates a IP ban.';
    }

    public function run(
        string $ip,
        #[ParameterAttribute(
            regex: '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'
        )]
        string $expires,
        string $message
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                ban_ip: objectParameter(
                    className: BanIp::class
                ),
            );
    }
}
