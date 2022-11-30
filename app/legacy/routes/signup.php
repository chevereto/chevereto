<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\captcha_check;
use Chevereto\Legacy\Classes\Confirmation;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\StopForumSpam;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\timing_safe_compare;
use function Chevereto\Legacy\generate_hashed_token;
use function Chevereto\Legacy\get_email_body_str;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Legacy\must_use_captcha;
use function Chevereto\Legacy\send_mail;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;

return function (Handler $handler) {
    $POST = post();
    $SAFE_POST = $handler::var('safe_post');
    if (!getSetting('enable_signups')) {
        $handler->issueError(404);

        return;
    }
    if ($POST !== [] && !$handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    } // Allow only 1 level
    if (Login::hasSignup()) {
        $SAFE_POST['email'] = Login::getSignup()['email'];
        redirect('account/awaiting-confirmation');
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    if ($logged_user) {
        redirect(User::getUrl($logged_user));
    }
    $failed_access_requests = $handler::var('failed_access_requests');
    $is_error = false;
    $input_errors = [];
    $error_message = null;
    $captcha_needed = $handler::cond('captcha_needed');
    if ($captcha_needed && $POST !== []) {
        $captcha = captcha_check();
        if (!$captcha->is_valid) {
            $is_error = true;
            $error_message = _s('%s says you are a robot', 'CAPTCHA');
        }
    }
    $handler::setCond('show_resend_activation', false);
    if ($POST !== [] && !$is_error && !Login::hasSignup()) {
        $__post = [];
        $__safe_post = [];
        foreach (['username', 'email'] as $v) {
            if (isset($POST[$v])) {
                $POST[$v] = $v == 'email' ? trim($POST[$v]) : strtolower(trim($POST[$v]));
                $__post[$v] = $POST[$v];
                $__safe_post[$v] = safe_html($POST[$v]);
            }
        }
        $handler::updateVar('post', $__post);
        $handler::updateVar('safe_post', $__safe_post);
        if (!filter_var($POST['email'], FILTER_VALIDATE_EMAIL)) {
            $input_errors['email'] = _s('Invalid email');
        }
        if (!User::isValidUsername($POST['username'])) {
            $input_errors['username'] = _s('Invalid username');
        }
        if (!preg_match('/' . getSetting('user_password_pattern') . '/', $POST['password'] ?? '')) {
            $input_errors['password'] = _s('Invalid password');
        }
        if (!filter_var($POST['email'], FILTER_VALIDATE_EMAIL)) {
            $input_errors['email'] = _s('Invalid email');
        }
        if ($POST['signup-accept-terms-policies'] != 1) {
            $input_errors['signup-accept-terms-policies'] = _s('You must agree to the terms and privacy policy');
        }
        if (getSetting('user_minimum_age') > 0 && !isset($POST['minimum-age-signup'])) {
            $input_errors['minimum-age-signup'] = _s('You must be at least %s years old to use this website.', getSetting('user_minimum_age'));
        }
        if (count($input_errors) > 0) {
            $is_error = true;
        } elseif (getSetting('stopforumspam')) {
            $sfs = new StopForumSpam(get_client_ip(), $POST['email'], $POST['username']);
            if ($sfs->isSpam()) {
                $is_error = true;
                $error_message = _s('Spam detected');
            }
        }
        if (!$is_error) {
            $user_db = DB::get('users', ['username' => $POST['username'], 'email' => $POST['email']], 'OR', []);
            if ($user_db !== []) {
                $is_error = true;
                $show_resend_activation = false;
                foreach ($user_db as $row) {
                    if (!in_array($row['user_status'], ['valid', 'banned'])) { // Don't touch the valid and banned users
                        $must_delete_old_user = false;
                        $confirmation_db = Confirmation::get(['user_id' => $row['user_id']]);
                        if ($confirmation_db !== false) {
                            // 24x2 = 48 tic tac tic tac
                            if (datetime_diff($confirmation_db['confirmation_date_gmt'], null, 'h') > 48) {
                                Confirmation::delete(['id' => $confirmation_db['confirmation_id']]);
                                $must_delete_old_user = true;
                            }
                        } else {
                            $must_delete_old_user = true;
                        }
                        if ($must_delete_old_user) {
                            DB::delete('users', ['id' => $row['user_id']]);

                            continue;
                        }
                    }
                    if (timing_safe_compare($row['user_username'], $POST['username'])) {
                        $input_errors['username'] = 'Username already being used';
                    }
                    if (timing_safe_compare($row['user_email'], $POST['email'])) {
                        $input_errors['email'] = _s('Email already being used');
                    }
                    if (!$show_resend_activation) {
                        $show_resend_activation = $row['user_status'] == 'awaiting-confirmation';
                    }
                }
                $handler::setCond('show_resend_activation', $show_resend_activation);
            } else {
                $user_array = [
                    'username' => $POST['username'],
                    'email' => $POST['email'],
                    'timezone' => getSetting('default_timezone'),
                    'language' => L10n::getLocale(),
                    'status' => getSetting('require_user_email_confirmation') ? 'awaiting-confirmation' : 'valid'
                ];

                try {
                    $inserted_user = User::insert($user_array);
                } catch (Exception $e) {
                    if ($e->getCode() === 666) {
                        $handler->issueError(403);

                        return;
                    } elseif ($e->getCode() === 999) {
                        $is_error = true;
                        $error_message = $e->getMessage();
                    } else {
                        throw new Exception($e, $e->getCode(), $e);
                    }
                }
                if (!$is_error) {
                    if ($inserted_user !== 0) {
                        $insert_password = Login::addPassword($inserted_user, $POST['password']);
                    }
                    if (!$inserted_user || !$insert_password) {
                        throw new Exception("Can't insert user to the DB", 400);
                    } elseif (getSetting('require_user_email_confirmation')) {
                        $hashed_token = generate_hashed_token($inserted_user);
                        Confirmation::insert([
                            'user_id' => $inserted_user,
                            'type' => 'account-activate',
                            'token_hash' => $hashed_token['hash'],
                            'status' => 'active'
                        ]);
                        $activation_link = get_public_url(
                            'account/activate/?token='
                            . $hashed_token['public_token_format']
                        );
                        global $theme_mail;
                        $theme_mail = [
                            'user' => $user_array,
                            'link' => $activation_link
                        ];
                        $mail['subject'] = _s('Confirmation required at %s', getSettings()['website_name']);
                        $mail['message'] = get_email_body_str('mails/account-confirm');
                        if (send_mail($POST['email'], $mail['subject'], $mail['message'])) {
                            $is_process_done = true;
                        }
                    } else {
                        $user = User::getSingle($inserted_user, 'id');
                        $logged_user = Login::login($user['id']);
                        Login::insertCookie('cookie', $inserted_user);

                        try {
                            global $theme_mail;
                            $theme_mail = [
                                'user' => $logged_user,
                                'link' => $logged_user['url']
                            ];

                            $mail['subject'] = _s('Welcome to %s', getSetting('website_name'));
                            $mail['message'] = get_email_body_str('mails/account-welcome');
                            send_mail($logged_user['email'], $mail['subject'], $mail['message']);
                        } catch (Exception $e) {
                        } // Silence
                        redirect($user['url']);
                    }
                    Login::setSignup([
                        'status' => 'awaiting-confirmation',
                        'email' => $SAFE_POST['email']
                    ]);
                    redirect('account/awaiting-confirmation');
                }
            }
        }
        if ($is_error) {
            RequestLog::insert([
                'type' => 'signup',
                'result' => 'fail'
            ]);
            $error_message = $error_message ?? _s('Check the errors in the form to continue.');
            if (getSettings()['captcha'] && must_use_captcha($failed_access_requests['day'] + 1)) {
                $captcha_needed = true;
            }
        }
    }
    $handler::setCond('error', $is_error);
    $handler::setCond('captcha_needed', $captcha_needed);
    $handler::setVar('pre_doctitle', _s('Create account'));
    $handler::setVar('error', $error_message);
    $handler::setVar('input_errors', $input_errors);
    $handler::setVar('signup_email', $SAFE_POST['email'] ?? null);
};
