<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Chevereto\File;

use Chevere\Throwable\Exceptions\LogicException;
use GuzzleHttp\Client;
use function Safe\file_put_contents;
use Throwable;

function storeDownloadedUrl(string $url, string $filepath)
{
    $clientArgs = [
        'base_uri' => $url,
        'timeout' => $_ENV['CHEVERETO_HTTP_TIMEOUT'] ?? 30,
    ];
    // @codeCoverageIgnoreStart
    if (isset($_ENV['CHEVERETO_HTTP_PROXY'])) {
        $clientArgs['proxy'] = $_ENV['CHEVERETO_HTTP_PROXY'];
    }
    // @codeCoverageIgnoreEnd
    try {
        $httpClient = new Client($clientArgs);
        $response = $httpClient->request('GET');
    } catch (Throwable $e) {
        throw new LogicException(previous: $e);
    }
    file_put_contents($filepath, $response->getBody());
}
