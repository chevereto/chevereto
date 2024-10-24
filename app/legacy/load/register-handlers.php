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
use Chevere\ThrowableHandler\ThrowableHandler;
use Chevere\VarDump\Formats\HtmlFormat;
use Chevere\VarDump\Formats\PlainFormat;
use Chevere\VarDump\Interfaces\FormatInterface;
use Chevere\VarDump\Outputs\HtmlOutput;
use Chevere\VarDump\Outputs\PlainOutput;
use Chevere\VarDump\VarDump;
use Chevereto\Config\Config;
use function Chevere\ThrowableHandler\throwableHandler;
use function Chevere\Writer\writers;
use function Chevere\xrDebug\PHP\throwableHandler as XrDebugThrowableHandler;
use function Chevereto\Legacy\isDebug;
use function Chevereto\Vars\env;
use function Chevereto\Vars\files;
use function Chevereto\Vars\get;
use function Chevereto\Vars\post;
use function Chevereto\Vars\server;

class HeadlessHtmlOutput extends HtmlOutput
{
    public function prepare(): void
    {
        $this->writer()->write(
            '<style>' . preg_replace('/\s+/', ' ', self::CSS) . '</style>'
        );
        $this->writer()->write(
            '<pre class="chv-dump">'
        );
    }

    public function writeCallerFile(FormatInterface $format): void
    {
        //
    }
}

class HeadlessPlainOutput extends PlainOutput
{
    public function prepare(): void
    {
    }

    public function finalize(): void
    {
    }

    public function writeCallerFile(FormatInterface $format): void
    {
    }
}

set_error_handler(ThrowableHandler::ERROR_AS_EXCEPTION); // @phpstan-ignore-line
register_shutdown_function(ThrowableHandler::SHUTDOWN_ERROR_AS_EXCEPTION);
set_exception_handler(function (Throwable $throwable) {
    $extra = '';
    $publicHandler = throwableHandler($throwable);
    $namespace = env()['CHEVERETO_ID_HANDLE']
        ?? false;
    if ($namespace) {
        $publicHandler = $publicHandler
            ->withId($namespace . $publicHandler->id());
    }
    if (PHP_SAPI === 'cli') {
        $docInternal = new ConsoleDocument($publicHandler);
        $parameters = [];
    } else {
        if (! headers_sent()) {
            http_response_code(500);
        }

        try {
            $debugLevel = Config::system()->debugLevel();
        } catch (Throwable) {
            $debugLevel = (int) ($_ENV['CHEVERETO_DEBUG_LEVEL'] ?? 1);
        }
        $doDebug = in_array($debugLevel, [2, 3], true) || isDebug();
        $publicHandler = $publicHandler->withIsDebug($doDebug);
        $internalHandler = $publicHandler->withIsDebug(true);
        $method = server()['REQUEST_METHOD'] ?? '';
        $uri = server()['REQUEST_URI'] ?? '';
        $uri = strtok($uri, '?');
        $internalHandler = $internalHandler->withPutExtra('URI', $uri);
        $internalHandler = $internalHandler->withPutExtra('Method', $method);
        $extra .= xrDebugExtraSection('URI', $uri);
        $extra .= xrDebugExtraSection('Method', $method);
        $parameters = [
            'POST' => post(),
            'GET' => get(),
            'FILES' => files(),
        ];
        $parameters = array_filter($parameters, fn ($value) => $value !== []);
        if ($parameters !== []) {
            $parametersHtml = (new VarDump(
                new HtmlFormat(),
                new HeadlessHtmlOutput()
            ))
                ->withVariables(...$parameters)
                ->export();
            $internalHandler = $internalHandler
                ->withPutExtra('Parameters', $parametersHtml);
            $extra .= xrDebugExtraSection('Parameters', $parametersHtml);
        }
        $docPublic = new HtmlDocument(
            $doDebug ? $internalHandler : $publicHandler
        );
        writers()->output()
            ->write($docPublic->__toString() . "\n");
    }
    $internalHandler = $internalHandler ?? $publicHandler;
    if ($parameters !== []) {
        $parametersPlain = (new VarDump(
            new PlainFormat(),
            new HeadlessPlainOutput()
        ))
            ->withVariables(...$parameters)
            ->export();
        $internalHandler = $internalHandler
            ->withPutExtra('Parameters', $parametersPlain);
        if (! isset($parametersHtml)) {
            $extra .= xrDebugExtraSection('Parameters', $parametersPlain);
        }
    }
    $docLogs = new PlainDocument($internalHandler);
    $logMessage = '[' . $publicHandler->id() . '] '
        . $docLogs->__toString()
        . "\n\n";

    try {
        $errorLog = ini_get('error_log');
        $fp = fopen($errorLog, 'a');
        fwrite($fp, $logMessage);
        fclose($fp);
    } catch (Throwable) {
        if (PHP_SAPI === 'cli') {
            error_log($logMessage);
        } else {
            writers()->error()->write($logMessage);
        }
    }
    $extra .= <<<HTML
    <div class="throwable-message"><b>Incident {$publicHandler->id()}</b></div>
    <div class="throwable-message"><b>Backtrace</b></div>
    HTML;
    XrDebugThrowableHandler($throwable, $extra);
    exit(255);
});

function xrDebugExtraSection(string $title, string $contents)
{
    return <<<HTML
    <div class="throwable-message">
        <b>{$title}</b>
        <div>{$contents}</div>
    </div>
    HTML;
}
