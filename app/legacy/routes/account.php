<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Config\Config;
use function Chevereto\Legacy\captcha_check;
use function Chevereto\Legacy\check_hashed_token;
use Chevereto\Legacy\Classes\Confirmation;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\TwoFactor;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\generate_hashed_token;
use function Chevereto\Legacy\get_email_body_str;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Legacy\hashed_token_info;
use function Chevereto\Legacy\must_use_captcha;
use function Chevereto\Legacy\send_mail;
use function Chevereto\Vars\get;
use function Chevereto\Vars\post;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    $POST = post();
    $handler->setTemplate('404');
    $route = $handler->requestArray()[0];
    $doing = $handler->request()[0] ?? false;
    if (!$doing || !in_array($doing, ['activate', 'password-reset', 'change-email-confirm', 'two-factor']) && $handler->isRequestLevel(3)) {
        $handler->issueError(404);

        return;
    }
    if (!Settings::get('enable_signups') && in_array($doing, ['awaiting-confirmation', 'activate', 'email-changed'])) {
        $handler->issueError(403);

        return;
    }
    $logged_user = Login::getUser();
    $loggedStatus = $logged_user['status'] ?? '';
    if (Login::isLoggedUser() && $doing !== 'email-needed' && $loggedStatus == 'awaiting-email') {
        redirect('account/email-needed');
    }

    switch ($doing) {
        case 'email-needed':
            if (Login::isLoggedUser() && $loggedStatus !== 'awaiting-email') {
                redirect($logged_user['url']);
            }

            break;
        case 'resend-activation':
        case 'activate':
            if (Login::isLoggedUser() && $loggedStatus !== 'awaiting-confirmation') {
                redirect($logged_user['url'] ?? '');
            }

            break;
        case 'two-factor':
            if (!Login::isLoggedUser()) {
                redirect('');
            }
            if (!TwoFactor::hasFor($logged_user['id'])) {
                redirect('settings/security');
            }
            if (!sessionVar()->hasKey('challenge_two_factor')) {
                redirect($logged_user['url'] ?? '');
            }

            break;
    }
    $captcha_needed = false;
    $request_to_db = [
        'password-forgot' => 'account-password-forgot',
        'password-reset' => 'account-password-forgot',
        'resend-activation' => 'account-activate',
        'activate' => 'account-activate',
        'email-needed' => 'account-email-needed',
        'change-email-confirm' => 'account-change-email',
        'two-factor' => 'account-two-factor',
    ];
    $request_db_field = $request_to_db[$doing] ?? '';
    $pre_doctitles = [
        'password-forgot' => _s('Forgot password?'),
        'password-reset' => _s('Reset password'),
        'resend-activation' => _s('Resend account activation'),
        'email-needed' => _s('Add your email address'),
        'awaiting-confirmation' => _s('Awaiting confirmation'),
        'two-factor' => _s('Two-factor authentication'),
        'email-changed' => _s('Email changed'),
    ];
    $keysToCheck = $request_to_db;
    unset($keysToCheck['change-email-confirm']);
    $keysToCheck = array_keys($keysToCheck);
    if (in_array($doing, $keysToCheck)) {
        $request_log = RequestLog::getCounts($request_db_field, 'fail');
        $captcha_needed = getSettings()['captcha']
            ? must_use_captcha($request_log['day'])
            : false;
    }
    $is_process_done = false;
    $is_error = false;
    $error_message = null;
    $input_errors = [];
    if ($captcha_needed && !empty($POST)) {
        $captcha = captcha_check();
        if (!$captcha->is_valid) {
            $is_error = true;
            $error_message = _s('%s says you are a robot', 'CAPTCHA');
        }
    }
    $handler->setTemplate($route . '/' . $doing);
    switch ($doing) {
        case 'password-forgot':
        case 'resend-activation':
            if ($doing == 'password-forgot' && $loggedStatus == 'valid' || $doing == 'resend-activation' && $loggedStatus == 'awaiting-confirmation') {
                $POST['user-subject'] = $logged_user['username'];
                $is_error = false;
            }
            if ($POST !== [] && !$is_error) {
                $subject_type = filter_var($POST['user-subject'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
                if (trim($POST['user-subject']) == '') {
                    $is_error = true;
                    $input_errors['user-subject'] = _s('Invalid Username/Email');
                }
                if (!$is_error) {
                    $user = User::getSingle($POST['user-subject'], $subject_type);
                    if ($user !== []) {
                        if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                            $error_message = _s("User doesn't have an email.");
                            $is_error = true;
                        }
                        if ($doing == 'password-forgot') {
                            switch ($user['status']) {
                                case 'banned':
                                    $handler->issueError(403);

                                    return;

                                    break;
                                case 'awaiting-email':
                                case 'awaiting-confirmation':
                                    $is_error = true;
                                    $error_message = _s('Account needs to be activated to use this feature');

                                    break;
                            }
                        } else { //'resend-activation'
                            switch ($user['status']) {
                                case 'valid':
                                    $is_error = true;
                                    $error_message = _s('Account already activated');

                                    break;
                                case 'banned':
                                    $is_error = true;
                                    $error_message = _s('Account banned');

                                    break;
                            }
                        }
                        if ($handler->template() == '403') {
                            RequestLog::insert([
                                'type' => $request_db_field,
                                'result' => 'fail',
                                'user_id' => $user['id']
                            ]);

                            return;
                        }
                    } else {
                        $is_error = true;
                        $input_errors['user-subject'] = _s('Invalid Username/Email');
                    }
                    if (!$is_error) {
                        $confirmation_db = Confirmation::get(['user_id' => $user['id'], 'type' => $request_db_field, 'status' => 'active'], ['field' => 'date', 'order' => 'desc'], 1);
                        if ($confirmation_db !== false) {
                            $minute_diff = $confirmation_db['confirmation_date_gmt'] ? datetime_diff($confirmation_db['confirmation_date_gmt'], null, 'm') : 15 + 1;
                            if ($minute_diff < 15) { // Mimic for the already submitted
                                $is_error = true;
                                $is_process_done = false;
                                $activation_email = $user['email'];
                                if ($subject_type == 'username') { // We won't disclose this email address
                                    $activation_email = preg_replace('/(?<=.).(?=.*@)/u', '*', $activation_email);
                                    $explode = explode('@', $activation_email);
                                    while (strlen($explode[0]) < 4) {
                                        $explode[0] .= '*';
                                    }
                                    $activation_email = implode('@', $explode);
                                }
                                $handler::setVar('resend_activation_email', $activation_email);
                                $error_message = _s('Allow up to 15 minutes for the email. You can try again later.');
                            } else {
                                Confirmation::delete(['user_id' => $user['id'], 'type' => $request_db_field]);
                            }
                        }
                    }
                    if (!$is_error) {
                        $hashed_token = generate_hashed_token((int) $user['id']);
                        $array_values = [
                            'type' => $request_db_field,
                            'date' => datetime(),
                            'date_gmt' => datetimegmt(),
                            'token_hash' => $hashed_token['hash'],
                        ];
                        if (!isset($user['confirmation_id'])) {
                            $array_values['user_id'] = $user['id'];
                            $confirmation_db_query = Confirmation::insert($array_values);
                        } else {
                            $confirmation_db_query = Confirmation::update($user['confirmation_id'], $array_values);
                        }
                        if ($confirmation_db_query) {
                            $recovery_link = get_public_url('account/' . ($doing == 'password-forgot' ? 'password-reset' : 'activate') . '/?token=' . $hashed_token['public_token_format']);
                            global $theme_mail;
                            $theme_mail = [
                                'user' => $user,
                                'link' => $recovery_link,
                            ];
                            if ($doing == 'password-forgot') {
                                $mail['subject'] = _s('Reset your password at %s', getSettings()['website_name']);
                            } else {
                                $mail['subject'] = _s('Confirmation required at %s', getSettings()['website_name']);
                            }
                            $mail['message'] = get_email_body_str('mails/account-' . ($doing == 'password-forgot' ? 'password-reset' : 'confirm'));
                            xr($mail);
                            if (send_mail($user['email'], $mail['subject'], $mail['message'])) {
                                $is_process_done = true;
                            }
                            if ($doing == 'resend-activation') {
                                Login::setSignup([
                                    'status' => 'awaiting-confirmation',
                                    'email' => $user['email'],
                                ]);
                                redirect('account/awaiting-confirmation');
                            }
                            $handler::setVar('password_forgot_email', $user['email']);
                        } else {
                            throw new Exception("Can't insert confirmation in DB", 400);
                        }
                    }
                }
                if ($is_error) {
                    RequestLog::insert([
                        'result' => 'fail',
                        'type' => $request_db_field,
                        'user_id' => $user['id'] ?? null
                    ]);
                    if (getSettings()['captcha']
                        && isset($request_log)
                        && must_use_captcha($request_log['day'] + 1)) {
                        $captcha_needed = true;
                    }
                    if (!$error_message) {
                        $error_message = _s('Invalid Username/Email');
                    }
                }
            }

            break;
        case 'awaiting-confirmation':
            if (!Login::hasSignup()) {
                $handler->issueError(403);

                return;

                break;
            }
            if (Login::getSignup()['status'] != 'awaiting-confirmation') {
                $handler->issueError(403);

                return;
            }
            $signup_email = Login::isLoggedUser()
                ? $logged_user['email']
                : Login::getSignup()['email'];
            $handler::setVar('signup_email', $signup_email);

            break;
        case 'password-reset':
        case 'activate':
        case 'change-email-confirm':
            $token = get()['token'] ?? '';
            $get_token_array = $token !== ''
                ? explode(':', $token)
                : [];
            if (isset($request_log)
                && $request_log['day'] > Config::limit()->invalidRequestsPerDay()) {
                $get_token_array = [];
            }
            if ($get_token_array === [] || count($get_token_array) !== 2) {
                RequestLog::insert([
                    'type' => $request_db_field,
                    'result' => 'fail',
                    'user_id' => null
                ]);
                $handler->issueError(403);

                return;
            }
            $user_id = decodeID($get_token_array[0]);
            $get_token = hashed_token_info(get()['token']);
            $confirmation_db = Confirmation::get(['type' => $request_db_field, 'user_id' => $get_token['id']]);
            if ($confirmation_db === false) {
                $handler->issueError(403);

                return;
            }
            $hash_match = check_hashed_token($confirmation_db['confirmation_token_hash'], get()['token']);
            if (datetime_diff($confirmation_db['confirmation_date_gmt'], null, 'h') > 48) {
                Confirmation::delete(['id' => $confirmation_db['confirmation_id']]);
                $confirmation_db = false;
            }
            if (!$hash_match || !$confirmation_db) {
                RequestLog::insert([
                    'type' => $request_db_field,
                    'result' => 'fail',
                    'user_id' => $user_id
                ]);
                $handler->issueError(403);

                return;
            }
            switch ($doing) {
                case 'activate':
                    User::update($confirmation_db['confirmation_user_id'], ['status' => 'valid']);
                    Confirmation::delete(['id' => $confirmation_db['confirmation_id']]);
                    $logged_user = Login::login($confirmation_db['confirmation_user_id']);
                    Login::insertCookie('cookie', $logged_user['id']);
                    global $theme_mail;
                    $theme_mail = [
                        'user' => $logged_user,
                    ];
                    $mail['subject'] = _s('Welcome to %s', getSettings()['website_name']);
                    $mail['message'] = get_email_body_str('mails/account-welcome');
                    if (send_mail($logged_user['email'], $mail['subject'], $mail['message'])) {
                        $is_process_done = true;
                    }
                    Login::unsetSignup();
                    redirect($logged_user !== [] ? User::getUrl($logged_user) : null);

                    break;
                case 'password-reset':
                    if ($POST !== []) {
                        if (!preg_match('/' . getSetting('user_password_pattern') . '/', $POST['new-password'] ?? '')) {
                            $input_errors['new-password'] = _s('Invalid password');
                        }
                        if ($POST['new-password'] !== $POST['new-password-confirm']) {
                            $input_errors['new-password-confirm'] = _s("Passwords don't match");
                        }
                        if (count($input_errors) == 0) {
                            if (Login::hasPassword($user_id)) {
                                $is_process_done = Login::changePassword($user_id, $POST['new-password']);
                            } else {
                                $is_process_done = Login::addPassword($user_id, $POST['new-password']);
                            }
                            if ($is_process_done) {
                                Confirmation::delete(['type' => $request_db_field, 'user_id' => $user_id]);
                            } else {
                                throw new Exception('Unexpected error', 400);
                            }
                        }
                    } else {
                        $is_process_done = false;
                    }

                    break;
                case 'change-email-confirm':
                    $email_candidate = $confirmation_db['confirmation_extra'];
                    $email_db = DB::get('users', ['email' => $email_candidate]);
                    if ($email_db !== []) {
                        if ($email_db['user_status'] == 'valid') {
                            Confirmation::delete(['id' => $confirmation_db['confirmation_id']]);
                            RequestLog::insert([
                                'type' => $request_db_field,
                                'result' => 'fail',
                                'user_id' => $user_id
                            ]);
                            $handler->issueError(403);

                            return;
                        } else {
                            DB::delete('users', ['id' => $email_db['user_id']]);
                            Confirmation::delete(['type' => 'account-change-email', 'user_id' => $email_db['user_id']]);
                        }
                    }
                    Confirmation::delete(['type' => 'account-change-email', 'user_id' => $user_id]);
                    sessionVar()->put('change-email-confirm', true);
                    User::update($user_id, ['email' => $email_candidate]);
                    Login::login($user_id);
                    redirect('account/email-changed');

                    break;
            }

            break;
        case 'email-needed':
            if ($POST !== [] && !$is_error) {
                if (!filter_var($POST['email'], FILTER_VALIDATE_EMAIL)) {
                    $is_error = true;
                    $input_errors['email'] = _s('Invalid email');
                }
                if (!$is_error) {
                    $user = User::getSingle($POST['email'], 'email');
                    if ($user !== []) {
                        $is_error = true;
                        $input_errors['email'] = _s('Email already being used');
                    }
                }
                if (!$is_error) {
                    User::update($logged_user['id'], ['status' => getSetting('require_user_email_confirmation') ? 'awaiting-confirmation' : 'valid', 'email' => trim($POST['email'])]);
                    if (!getSetting('require_user_email_confirmation')) {
                        redirect($logged_user['url']);
                    }
                    $hashed_token = generate_hashed_token((int) $logged_user['id']);
                    $array_values = [
                        'type' => 'account-activate',
                        'date' => datetime(),
                        'date_gmt' => datetimegmt(),
                        'token_hash' => $hashed_token['hash'],
                    ];
                    $array_values['user_id'] = $logged_user['id'];
                    $confirmation_db_query = Confirmation::insert($array_values);
                    $activation_link = get_public_url('account/activate/?token=' . $hashed_token['public_token_format']);
                    global $theme_mail;
                    $theme_mail = [
                        'user' => $logged_user,
                        'link' => $activation_link,
                    ];
                    $mail['subject'] = _s('Confirmation required at %s', getSettings()['website_name']);
                    $mail['message'] = get_email_body_str('mails/account-confirm');
                    if (send_mail($POST['email'], $mail['subject'], $mail['message'])) {
                        $is_process_done = true;
                    }
                    Login::setSignup([
                        'status' => 'awaiting-confirmation',
                        'email' => $POST['email'],
                    ]);
                    redirect('account/awaiting-confirmation');
                } else {
                    RequestLog::insert(
                        [
                            'result' => 'fail',
                            'type' => $request_db_field,
                            'user_id' => $user['id'] ?? null
                        ]
                    );
                    if (getSettings()['captcha']
                        && isset($request_log)
                        && must_use_captcha($request_log['day'] + 1)) {
                        $captcha_needed = true;
                    }
                }
                if ($is_error) {
                    $error_message = $input_errors['email'];
                }
            }

            break;
        case 'email-changed':
            if (!isset(session()['change-email-confirm'])) {
                $handler->issueError(404);

                return;
            }
            $handler->setTemplate($route . '/' . 'email-changed');

            break;
        case 'two-factor':
            $handler->setTemplate($route . '/' . 'two-factor');
            if (!is_null($POST['user-two-factor'] ?? null) && !$is_error) {
                $twoFactor = (new TwoFactor())->withSecret(
                    TwoFactor::getSecretFor(intval($logged_user['id']))
                );
                if ($twoFactor->verify($POST['user-two-factor'])) {
                    sessionVar()->remove('challenge_two_factor');
                    redirect($logged_user['url']);
                } else {
                    $is_error = true;
                    $input_errors['user-two-factor'] = _s('Invalid code');
                    RequestLog::insert([
                        'type' => $request_db_field,
                        'result' => 'fail',
                        'user_id' => $logged_user['id']
                    ]);
                    if (getSettings()['captcha']
                        && isset($request_log)
                        && must_use_captcha($request_log['day'] + 1)) {
                        $captcha_needed = true;
                    }
                }
            }

            break;
        default:
            $handler->issueError(404);

            return;
    }
    $handler::setVar('pre_doctitle', $pre_doctitles[$doing]);
    $handler::setCond('error', $is_error);
    $handler::setCond('process_done', $is_process_done);
    $handler::setVar('input_errors', $input_errors);
    $handler::setVar('error', $error_message ?? _s('Check the errors in the form to continue.'));
    $handler::setCond('captcha_needed', $captcha_needed);
};
