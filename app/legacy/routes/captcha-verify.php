<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\fetch_url;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\get;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    $key = getSetting('captcha_secret') ?? '';
    if ($key === '') {
        redirect('', 302);
    }

    try {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/json; charset=UTF-8');
        $endpoint = match (getSetting('captcha_api')) {
            '3' => 'https://www.recaptcha.net/recaptcha/api/siteverify',
            default => throw new Exception('Invalid captcha type'),
        };
        $params = [
            'secret' => getSetting('captcha_secret'),
            'response' => get()['token'] ?? '',
            'remoteip' => get_client_ip(),
        ];
        $fetch = fetch_url(
            url: $endpoint,
            options: [
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => http_build_query($params),
            ]
        );
        $object = json_decode($fetch);
        $isSuccess = (bool) $object->success;
        sessionVar()->put('isHuman', $isSuccess);
        sessionVar()->put('isBot', ! $isSuccess);
        exit($fetch);
    } catch (Exception) {
    }
    exit();
};
