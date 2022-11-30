<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevere\ThrowableHandler\Documents\ConsoleDocument;
use Chevere\ThrowableHandler\Documents\HtmlDocument;
use Chevere\ThrowableHandler\Documents\PlainDocument;
use function Chevere\ThrowableHandler\throwableHandler;
use Chevere\ThrowableHandler\ThrowableHandler;
use function Chevere\Writer\writers;
use function Chevere\Xr\throwableHandler as XrThrowableHandler;
use Chevereto\Config\Config;
use function Chevereto\Legacy\isDebug;

set_error_handler(ThrowableHandler::ERROR_AS_EXCEPTION);
register_shutdown_function(ThrowableHandler::SHUTDOWN_ERROR_AS_EXCEPTION);
set_exception_handler(function (Throwable $throwable) {
    $throwableHandler = throwableHandler($throwable);
    if (PHP_SAPI === 'cli') {
        $docInternal = new ConsoleDocument($throwableHandler);
    } else {
        $docInternal = new PlainDocument($throwableHandler);
        if (!headers_sent()) {
            http_response_code(500);
        }
        $isDebug = isDebug();

        try {
            $debugLevel = Config::system()->debugLevel();
        } catch (Throwable) {
            $debugLevel = (int) ($_ENV['CHEVERETO_DEBUG_LEVEL'] ?? 1);
        }

        $docPublic = new HtmlDocument(
            $throwableHandler->withIsDebug(
                in_array($debugLevel, [2, 3]) || $isDebug
            )
        );
        writers()->output()
            ->write($docPublic->__toString() . "\n");
    }
    writers()->error()
        ->write($docInternal->__toString() . "\n\n");

    XrThrowableHandler(
        $throwable,
        <<<HTML
        <div class="throwable-message">Incident ID: {$throwableHandler->id()}</div>
        HTML
    );
    die(255);
});
