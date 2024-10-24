<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Tag;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\json_output;
use function Chevereto\Vars\get;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    header('Content-type: application/json; charset=UTF-8');
    if (! $handler::checkAuthToken(request()['auth_token'] ?? '')) {
        json_output(401, [
            'error' => [
                'error_msg' => _s('Request denied'),
            ],
        ]);
    }
    if ($handler->isRequestLevel(2)) {
        json_output(404);

        return;
    }

    $q = get()['q'] ?? '';
    $q = trim($q);
    if ($q === ''
        || str_contains($q, ',')
    ) {
        json_output(400);

        return;
    }
    $array = [
        'items' => Tag::autocomplete($q),
    ];
    json_output(200, $array);
};
