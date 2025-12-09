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

use Chevereto\Tenants\TenantsApiKey;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TenantsApiKeyAuthorization implements MiddlewareInterface
{
    public function __construct(
        private TenantsApiKey $tenantsApiKey
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $key = $request->getHeaderLine('X-API-Key');
        if ($key === '') {
            return (new Psr17Factory())
                ->createResponse(
                    401,
                    'Missing X-API-Key header'
                );
        }

        try {
            $this->tenantsApiKey->assert($key);
        } catch (InvalidArgumentException $e) {
            return (new Psr17Factory())
                ->createResponse(
                    401,
                    'Invalid API key'
                );
        }

        return $handler->handle($request);
    }
}
