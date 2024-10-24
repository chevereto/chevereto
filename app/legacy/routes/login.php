<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\TwoFactor;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\captcha_check;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\must_use_captcha;
use function Chevereto\Vars\env;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    if (post() !== [] && ! $handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    } // Allow only 1 level
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    if ($logged_user) {
        redirect(User::getUrl($logged_user), 302);
    }
    $request_log_insert = [
        'type' => 'login',
        'user_id' => null,
    ];
    $failed_access_requests = $handler::var('failed_access_requests');
    $SAFE_POST = $handler::var('safe_post');
    $is_error = false;
    $captcha_needed = $handler::cond('captcha_needed');
    $error_message = null;
    if ($captcha_needed && ! empty(post())) {
        $captcha = captcha_check();
        if (! $captcha->is_valid) {
            $is_error = true;
            $error_message = _s('%s says you are a robot', 'CAPTCHA');
        }
    }
    if (post() !== [] && ! $is_error) {
        $login_by = filter_var(post()['login-subject'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (trim(post()['login-subject']) == '' || trim(post()['password']) == '') {
            $is_error = true;
        }
        if (! $is_error) {
            $user = User::getSingle(
                trim(post()['login-subject']),
                $login_by,
                true
            );
            if ($user !== []) {
                $user['id'] = (int) $user['id'];
                $request_log_insert['user_id'] = $user['id'];
                switch ($user['status']) {
                    case 'awaiting-confirmation':
                        Login::setSignup([
                            'status' => 'awaiting-confirmation',
                            'email' => $user['email'],
                        ]);
                        redirect('account/awaiting-confirmation', 302);

                        break;
                    case 'banned':
                        $handler->issueError(403);

                        return;
                }
                $is_login = ! (bool) env()['CHEVERETO_ENABLE_USERS'] && getSetting('website_mode_personal_uid') != $user['id']
                    ? false
                    : Login::checkPassword($user['id'], post()['password']);
            }
            if ($is_login ?? false) {
                $request_log_insert['result'] = 'success';
                RequestLog::insert($request_log_insert);
                $logged_user = Login::login($user['id']);
                Login::insertCookie('cookie', $user['id']);
                $redirect_to = User::getUrl(Login::getUser(), true);
                if (TwoFactor::hasFor($user['id'])) {
                    sessionVar()->put('challenge_two_factor', $user['id']);
                    $redirect_to = 'account/two-factor';
                } elseif (isset(session()['last_url'])) {
                    $redirect_to = session()['last_url'];
                }
                if ($user['status'] == 'awaiting-email') {
                    $redirect_to = 'account/email-needed';
                }

                redirect($redirect_to, 302);
            } else {
                $is_error = true;
            }
        }
        if ($is_error) {
            $request_log_insert['result'] = 'fail';
            RequestLog::insert($request_log_insert);
            $error_message = _s('Wrong Username/Email password combination');
            if ((getSetting('captcha') ?? false) && must_use_captcha($failed_access_requests['day'] + 1)) {
                $captcha_needed = true;
            }
        }
    }
    $handler::setCond('error', $is_error);
    $handler::setCond('captcha_needed', $captcha_needed);
    $handler::setVar('pre_doctitle', _s('Sign in'));
    $handler::setVar('error', $error_message);
};
