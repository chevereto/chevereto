<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use LogicException;
use function Chevereto\Legacy\G\curlResolveCa;
use function Chevereto\Legacy\G\get_mimetype;

/**
 * https://shield.projectarachnid.com/docs
 */
class ProjectArachnid
{
    public const CLASSIFICATION = [
        'csam' => 0,
        'harmful-abusive-material' => 0,
        'no-known-match' => 1,
    ];

    private array $scan = [];

    private string $errorMessage = '';

    private int $errorCode = 0;

    public function __construct(
        string $apiUsername,
        string $apiPassword,
        private string $filePath
    ) {
        $url = 'https://shield.projectarachnid.com/v1/media';
        $this->errorMessage = '';
        $this->errorCode = 0;
        $ch = curl_init();
        curlResolveCa($ch);
        $auth = base64_encode("{$apiUsername}:{$apiPassword}");
        $mimetype = get_mimetype($filePath);
        $curlFile = file_get_contents($filePath);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_FAILONERROR => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $curlFile,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                "Authorization: Basic {$auth}",
                "Content-Type: {$mimetype}",
            ],
        ]);
        $curl_response = curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            $error_msg = curl_error($ch);
        }
        if ($curl_response === false) {
            $this->errorMessage = $error_msg ?? '';
            $this->errorCode = 100;
        } else {
            $json = json_decode($curl_response, true);
            if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->errorMessage = 'Malformed Project Arachnid response';
                $this->errorCode = 200;
            } else {
                $this->scan = $json;
            }
        }
        curl_close($ch);
    }

    public function scan(): array
    {
        return $this->scan;
    }

    public function isSuccess(): bool
    {
        return $this->errorCode === 0;
    }

    public function errorCode(): int
    {
        return $this->errorCode;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    public function assertIsAllowed(): void
    {
        $key = $this->scan['classification'] ?? 'no-known-match';
        $flag = static::CLASSIFICATION[$key] ?? 1;
        if ($flag !== 0) {
            return;
        }

        throw new LogicException(
            _s('CSAM content is forbidden'),
            403
        );
    }
}
