<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Encoding;

use function Chevere\Message\message;
use Chevere\Regex\Interfaces\RegexInterface;
use Chevere\Regex\Regex;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use Chevere\Throwable\Exceptions\RuntimeException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StreamException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_filter_append;

/**
 * @throws InvalidArgumentException
 */
function assertBase64(string $string): void
{
    $double = base64_encode(base64_decode($string, true));
    if ($string !== $double) {
        // @codeCoverageIgnoreStart
        throw new InvalidArgumentException(
            message('Invalid base64 formatting'),
            600
        );
        // @codeCoverageIgnoreEnd
    }
    unset($double);
}

/**
 * @param string $base64 A base64 encoded string
 * @param string $filepath Filename or stream to store decoded base64
 *
 * @throws FilesystemException
 * @throws StreamException
 * @throws RuntimeException
 */
function storeDecodedBase64(string $base64, string $filepath): void
{
    $filter = 'convert.base64-decode';
    $fh = fopen($filepath, 'w');
    stream_filter_append($fh, $filter, STREAM_FILTER_WRITE);
    if (fwrite($fh, $base64) === 0) {
        // @codeCoverageIgnoreStart
        throw new RuntimeException(
            message('Unable to write %filter% provided string')
                ->withCode('%filter%', $filter),
            1200
        );
        // @codeCoverageIgnoreEnd
    }
    fclose($fh);
}

function getBase64Regex(): RegexInterface
{
    return new Regex('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/');
}
