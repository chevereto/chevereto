<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevere\VarDump\VarDumpInstance;
use Chevere\Writer\StreamWriter;
use Chevere\Writer\Writers;
use Chevere\Writer\WritersInstance;
use function Chevere\VarDump\varDumpHtml;
use function Chevere\Writer\streamFor;

try {
    define('TIME_EXECUTION_START', microtime(true));
    ! defined('REPL') && define('REPL', false);
    ! defined('PACKAGING') && define('PACKAGING', false);
    require_once __DIR__ . '/../../vendor/autoload.php';
    new WritersInstance(
        (new Writers())
            ->withOutput(
                new StreamWriter(
                    streamFor('php://output', 'w')
                )
            )
            ->withError(
                new StreamWriter(
                    streamFor('php://stderr', 'w')
                )
            )
    );
    if (PHP_SAPI !== 'cli') {
        new VarDumpInstance(varDumpHtml());
    }
    require_once __DIR__ . '/register-handlers.php';
    $posix_getuid = function_exists('posix_getuid')
        ? posix_getuid()
        : 'unknown';
} catch (Throwable $e) {
    $error = sprintf(
        '[CHEVERETO LOAD ERROR] %s: %s %s',
        $e::class,
        $e->getMessage(),
        $e->getFile() . ':' . $e->getLine()
    );
    error_log($error);
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
    }

    echo 'An error occurred while loading Chevereto.' . PHP_EOL;
    exit(255);
}
