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

final class SignedRequest implements MiddlewareInterface
{
    use MiddlewareNameWithArgumentsTrait;

    private string $secret = '';

    public function setUp(string $secret): void
    {
        $this->secret = $secret;
    }

    public static function with(string $secret): MiddlewareNameInterface
    {
        return static::middlewareName(...get_defined_vars());
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if ($this->secret === '') {
            return $handler->handle($request);
        }
        $hexSignature = $request->getHeaderLine('X-Signature');
        if ($hexSignature === '') {
            return (new Psr17Factory())
                ->createResponse(
                    401,
                    'Missing X-Signature header'
                );
        }

        $payload = (string) $request->getBody();
        $computed = hash_hmac('sha256', $payload, $this->secret);
        $verified = hash_equals($computed, $hexSignature);
        if (! $verified) {
            return (new Psr17Factory())
                ->createResponse(
                    401,
                    'Invalid request signature'
                );
        }

        return $handler->handle($request);
    }
}
