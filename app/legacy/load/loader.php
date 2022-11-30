<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\RuntimeException;
use function Chevere\VarDump\varDumpHtml;
use Chevere\VarDump\VarDumpInstance;
use function Chevere\Writer\streamFor;
use Chevere\Writer\StreamWriter;
use Chevere\Writer\Writers;
use Chevere\Writer\WritersInstance;

define('TIME_EXECUTION_START', microtime(true));
!defined('REPL') && define('REPL', false);
!defined('PACKAGING') && define('PACKAGING', false);
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
                streamFor('php://stderr', 'a')
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
if ($posix_getuid === 0
    && !(REPL || PACKAGING)) { // @phpstan-ignore-line
    $message = 'Unable to run as root (run this command as a regular user)';
    if (PHP_SAPI === 'cli') {
        echo "[ERROR] $message\n";
        die(255);
    }

    throw new RuntimeException(
        message($message)
    );
}
