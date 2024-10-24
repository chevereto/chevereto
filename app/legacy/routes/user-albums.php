<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Legacy\json_output;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    header('Content-type: application/json; charset=UTF-8');
    json_output(404);
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
    $logged_user = Login::getUser();
    if ($logged_user === []) {
        json_output(401);

        return;
    }
    $user = User::getSingle($q, 'username', false);
    $user = DB::formatRow($user);
    $user['url'] = User::getUrl($user);
    if (! $user
        || ($user['status'] ?? '') !== 'valid'
        && ($logged_user === [] || ! $handler::cond('content_manager'))
    ) {
        json_output(404);

        return;
    }
    $is_owner = false;
    if (isset($user['id'], $logged_user['id'])) {
        $is_owner = $user['id'] === $logged_user['id'];
    }
    if (! $is_owner
        && ! $handler::cond('content_manager')
        && (bool) $user['is_private']
    ) {
        json_output(404);

        return;
    }
    if (! (bool) env()['CHEVERETO_ENABLE_USERS']
        && $user['id'] !== getSettings('website_mode_personal_uid')
    ) {
        json_output(404);

        return;
    }
    $array = User::getAlbums($user);
    json_output(200, $array);
};
