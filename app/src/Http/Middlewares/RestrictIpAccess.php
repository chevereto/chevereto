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

namespace Chevereto\Http\Middlewares;

use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Chevere\Http\Traits\MiddlewareNameWithArgumentsTrait;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Chevereto\Legacy\isIpAllowed;

final class RestrictIpAccess implements MiddlewareInterface
{
    use MiddlewareNameWithArgumentsTrait;

    private string $allowList;

    public function setUp(string $allowList): void
    {
        $this->allowList = $allowList;
    }

    public static function with(string $allowList): MiddlewareNameInterface
    {
        return static::middlewareName(...get_defined_vars());
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if ($this->allowList === '') {
            return $handler->handle($request);
        }
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        if ($remoteAddress === '') {
            return (new Psr17Factory())
                ->createResponse(
                    400,
                    'Unable to determine client IP address'
                );
        }
        $isAllowed = isIpAllowed($remoteAddress, $this->allowList);
        if (! $isAllowed) {
            return (new Psr17Factory())
                ->createResponse(
                    403,
                    'Access denied from your IP address'
                );
        }

        return $handler->handle($request);
    }
}
