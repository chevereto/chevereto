<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Akismet;
use Chevereto\Legacy\Classes\ApiKey;
use Chevereto\Legacy\Classes\Confirmation;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\IpBan;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\TwoFactor;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\array_filter_array;
use function Chevereto\Legacy\G\dateinterval;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\is_url_web;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\generate_hashed_token;
use function Chevereto\Legacy\get_available_languages;
use function Chevereto\Legacy\getIpButtonsArray;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Legacy\send_mail;
use function Chevereto\Vars\env;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    $POST = post();
    if ($POST !== [] and !$handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    $logged_user = Login::getUser();
    if ($logged_user === []) {
        redirect('login');
    }
    User::statusRedirect($logged_user['status'] ?? null);
    $handler->setTemplate('settings');
    $is_dashboard_user = $handler::cond('dashboard_user');
    if (!$is_dashboard_user) {
        RequestLog::getCounts('account-edit', 'fail');
    }
    $allowed_to_edit = ['name', 'username', 'email', 'avatar_filename', 'website', 'background_filename', 'timezone', 'language', 'image_keep_exif', 'image_expiration', 'newsletter_subscribe', 'bio', 'show_nsfw_listings', 'is_private', 'status'];
    if ($is_dashboard_user) {
        $allowed_to_edit = array_merge($allowed_to_edit, ['is_admin', 'is_manager']);
    }
    if (!getSetting('enable_expirable_uploads')) {
        $key = array_search('image_expiration', $allowed_to_edit);
        unset($allowed_to_edit[$key]);
    }
    $user = $is_dashboard_user
        ? User::getSingle($handler->request()[1], 'id')
        : $logged_user;
    if ($user === []) {
        $handler->issueError(404);

        return;
    }
    $is_owner = $user['id'] == Login::getUser()['id'];
    if ($is_dashboard_user && $user['is_content_manager'] && Login::isAdmin() == false) {
        $handler->issueError(404);

        return;
    }
    if (in_array('language', $allowed_to_edit)
        && isset($POST['language'])
        && $logged_user['language'] !== $POST['language']
        && $logged_user['id'] == $user['id']
        && array_key_exists($POST['language'], L10n::getEnabledLanguages())
    ) {
        L10n::processTranslation($POST['language']);
    }
    $routes = [
        'account' => _s('Account'),
        'profile' => _s('Profile'),
        'password' => _s('Password'),
        'security' => _s('Security'),
        'api' => 'API',
        'connections' => _s('Connections'),
        'homepage' => _s('Homepage'),
        'powered' => _s('Powered by'),
    ];
    $icons = [
        'account' => 'fas fa-user',
        'profile' => 'fas fa-id-card',
        'api' => 'fas fa-project-diagram',
        'password' => 'fas fa-key',
        'security' => 'fas fa-shield-alt',
        'connections' => 'fas fa-plug',
        'homepage' => 'fas fa-home',
        'powered' => 'fas fa-power-off',
    ];
    $default_route = 'account';
    $route_homepage = false;
    if (getSetting('website_mode') == 'personal' and getSetting('website_mode_personal_routing') !== '/' and $logged_user['id'] == getSetting('website_mode_personal_uid')) {
        $route_homepage = true;
    }
    $is_email_required = (bool) getSetting('require_user_email_confirmation');
    if ($handler::cond('content_manager') && $is_owner == false) {
        $is_email_required = false;
    }
    $providersEnabled = Login::getProviders('enabled');
    if ($is_email_required && getSetting('require_user_email_social_signup') == false) {
        foreach (array_keys($providersEnabled) as $k) {
            if (array_key_exists($k, $user['login'])) {
                $is_email_required = false;

                break;
            }
        }
    }
    $doing_level = $is_dashboard_user ? 2 : 0;
    $doing = $handler->request()[$doing_level] ?? $default_route;
    if (!$user || isset($handler->request()[$doing_level + 1]) || (!is_null($doing) and !array_key_exists($doing, $routes))) {
        $handler->issueError(404);

        return;
    }
    if ($doing == '') {
        $doing = $default_route;
    }
    $tabs = [];
    foreach ($routes as $route => $label) {
        $aux = str_replace('_', '-', $route);
        $handler::setCond('settings_' . $aux, $doing == $aux);
        if ($handler::cond('settings_' . $aux)) {
            $handler::setVar('setting', $aux);
        }
        if ($aux == 'homepage' and !$route_homepage) {
            continue;
        }
        $tabs[$aux] = [
            'icon' => $icons[$route],
            'label' => $label,
            'url' => get_base_url(
                ($is_dashboard_user ? ('dashboard/user/' . $user['id']) : 'settings')
                . ($route == $default_route ? '' : '/' . $route)
            ),
            'current' => $handler::cond('settings_' . $aux)
        ];
    }
    if (count($providersEnabled) == 0 || ($is_dashboard_user && Login::isAdmin() == false)) {
        unset($routes['connections']);
        $tabs = array_filter_array($tabs, ['connections'], 'rest');
    }
    $handler::setVar('tabs', $tabs);

    if (!array_key_exists($doing, $routes)) {
        $handler->issueError(404);

        return;
    }
    $SAFE_POST = $handler::var('safe_post');
    $is_error = false;
    $is_changed = false;
    $captcha_needed = false;
    $input_errors = [];
    $error_message = null;
    $changed_email_message = null;
    if ($POST !== []) {
        $field_limits = 255;
        foreach ($allowed_to_edit as $k) {
            if (isset($POST[$k])) {
                $POST[$k] = substr($POST[$k], 0, $field_limits);
            }
        }
        switch ($doing) {
            case null:
            case 'account':
                $checkboxes = ['upload_image_exif', 'newsletter_subscribe', 'show_nsfw_listings', 'is_private'];
                foreach ($checkboxes as $k) {
                    if (!isset($POST[$k])) {
                        continue;
                    }
                    $POST[$k] = in_array($POST[$k], ['On', 1]) ? 1 : 0;
                }
                nullify_string($POST['image_expiration']);
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
                if (!User::isValidUsername($POST['username'])) {
                    $input_errors['username'] = _s('Invalid username');
                }
                if ($is_email_required and !filter_var($POST['email'], FILTER_VALIDATE_EMAIL)) {
                    $input_errors['email'] = _s('Invalid email');
                }
                if (getSetting('enable_expirable_uploads')) {
                    if ($POST['image_expiration'] !== null && (!dateinterval($POST['image_expiration']) || !array_key_exists($POST['image_expiration'], Image::getAvailableExpirations()))) {
                        $input_errors['image_expiration'] = _s('Invalid image expiration: %s', $POST['image_expiration']);
                    }
                }
                if (getSetting('language_chooser_enable') && !array_key_exists($POST['language'], get_available_languages())) {
                    $POST['language'] = getSetting('default_language');
                }
                if (!in_array($POST['timezone'], timezone_identifiers_list())) {
                    $POST['timezone'] = date_default_timezone_get();
                }
                if (is_array($input_errors) && count($input_errors) > 0) {
                    $is_error = true;
                }
                if (!$is_error) {
                    $user_db = DB::get('users', ['username' => $POST['username'], 'email' => $POST['email']], 'OR', []);
                    if ($user_db) {
                        foreach ($user_db as $row) {
                            if ($row['user_id'] == $user['id']) {
                                continue;
                            } // Same guy?
                            if (!in_array($row['user_status'], ['valid', 'banned'])) { // Don't touch the valid and banned users
                                $must_delete_old_user = false;
                                $confirmation_db = Confirmation::get(['user_id' => $row['user_id']]);
                                if ($confirmation_db !== false) {
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
                            if (hash_equals((string) $row['user_username'], (string) $POST['username'])
                                && $user['username'] !== $row['user_username']
                            ) {
                                $input_errors['username'] = 'Username already being used';
                            }
                            if (!empty($POST['email'])
                                && hash_equals((string) $row['user_email'], (string) $POST['email'])
                                && $user['email'] !== $row['user_email']
                            ) {
                                $input_errors['email'] = _s('Email already being used');
                            }
                        }
                        if (count($input_errors) > 0) {
                            $is_error = true;
                        }
                    }
                }
                if (!$is_error
                    && $is_email_required
                    && !empty($POST['email'])
                    && !hash_equals(
                        (string) ($user['email'] ?? ''),
                        (string) $POST['email']
                    )
                ) {
                    Confirmation::delete(['type' => 'account-change-email', 'user_id' => $user['id']]);
                    $hashed_token = generate_hashed_token((int) $user['id']);
                    Confirmation::insert([
                        'type' => 'account-change-email',
                        'user_id' => $user['id'],
                        'token_hash' => $hashed_token['hash'],
                        'status' => 'active',
                        'extra' => $POST['email']
                    ]);
                    $email_confirm_link = get_public_url(
                        'account/change-email-confirm/?token='
                        . $hashed_token['public_token_format']
                    );
                    $changed_email_message = _s('An email has been sent to %s with instructions to activate this email', $SAFE_POST['email']);
                    global $theme_mail;
                    $theme_mail = [
                        'user' => $user,
                        'link' => $email_confirm_link
                    ];
                    ob_start();
                    require_once PATH_PUBLIC_LEGACY_THEME . 'mails/account-change-email.php';
                    $mail_body = ob_get_contents();
                    ob_end_clean();
                    $mail['subject'] = _s('Confirmation required at %s', getSettings()['website_name']);
                    $mail['message'] = $mail_body;
                    send_mail($POST['email'], $mail['subject'], $mail['message']);
                    unset($POST['email']);
                }

                break;
            case 'profile':
                if (!preg_match('/^.{1,60}$/', $POST['name'] ?? '')) {
                    $input_errors['name'] = _s('Invalid name');
                }
                if (!empty($POST['website'])) {
                    if (!is_url_web($POST['website'])) {
                        $input_errors['website'] = _s('Invalid website');
                    }
                }
                if (!$handler::cond('content_manager') && getSetting('akismet')) {
                    $akismet = new Akismet();
                    $isSpam = $akismet->isSpam($POST['bio'], $POST['name'], $user['email'], $POST['website']);
                    $is_error = $isSpam;
                    $error_message = _s('Spam detected');
                }

                break;

            case 'password':
                if (!$is_dashboard_user) {
                    if (isset($POST['current-password'])) {
                        if (!Login::checkPassword($user['id'], $POST['current-password'])) {
                            $input_errors['current-password'] = _s('Wrong password');
                        }
                        if ($POST['current-password'] == ($POST['new-password'] ?? null)) {
                            $input_errors['new-password'] = _s('Use a new password');
                            $handler::updateVar('safe_post', ['current-password' => null]);
                        }
                    }
                }
                if (!preg_match('/' . getSetting('user_password_pattern') . '/', $POST['new-password'] ?? '')) {
                    $input_errors['new-password'] = _s('Invalid password');
                }
                if ($POST['new-password'] !== $POST['new-password-confirm']) {
                    $input_errors['new-password-confirm'] = _s("Passwords don't match");
                }

                break;

            case 'security':
                if (!TwoFactor::hasFor($user['id']) && sessionVar()->hasKey('two_factor_secret')) {
                    $twoFactor = new TwoFactor();
                    $twoFactor = $twoFactor->withSecret(session()['two_factor_secret']);
                    sessionVar()->remove('two_factor_secret');
                    if (!$twoFactor->verify($POST['two-factor-code'])) {
                        $input_errors['two-factor-code'] = _s('Invalid code');
                    } else {
                        $twoFactor->insert($user['id']);
                    }
                }

                break;

            case 'homepage':
                if (!array_key_exists($doing, $routes)) {
                    $handler->issueError(404);

                    return;
                }
                $allowed_to_edit = ['homepage_title_html', 'homepage_paragraph_html', 'homepage_cta_html'];
                $editing_array = array_filter_array($POST, $allowed_to_edit, 'exclusion');
                $update_settings = [];
                foreach ($allowed_to_edit as $k) {
                    if (!array_key_exists($k, Settings::get()) or Settings::get($k) == $editing_array[$k]) {
                        continue;
                    }
                    $update_settings[$k] = $editing_array[$k];
                }
                if ($update_settings !== []) {
                    $db = DB::getInstance();
                    $db->beginTransaction();
                    $db->query('UPDATE ' . DB::getTable('settings') . ' SET setting_value = :value WHERE setting_name = :name;');
                    foreach ($update_settings as $k => $v) {
                        $db->bind(':name', $k);
                        $db->bind(':value', $v);
                        $db->exec();
                    }
                    if ($db->endTransaction()) {
                        $is_changed = true;
                        foreach ($update_settings as $k => $v) {
                            Settings::setValue($k, $v);
                        }
                    }
                }

                break;

            default:
                $handler->issueError(404);

                return;

                break;
        }
        if (is_array($input_errors) && count($input_errors) > 0) {
            $is_error = true;
        }
        if (!$is_error) {
            if (in_array($doing, [null, 'account', 'profile'])) {
                foreach ($POST as $k => $v) {
                    if (($user[$k] ?? null) !== $v) {
                        $is_changed = true;
                    }
                }
                if ($is_changed) {
                    $editing_array = array_filter_array($POST, $allowed_to_edit, 'exclusion');
                    if (!$is_dashboard_user) {
                        unset($editing_array['status'], $editing_array['is_admin'], $editing_array['is_manager']);
                    } else {
                        if (!in_array($editing_array['status'] ?? null, ['valid', 'banned', 'awaiting-confirmation', 'awaiting-email'])) {
                            unset($editing_array['status']);
                        }
                        if ($logged_user['is_manager']) {
                            unset($POST['email'], $editing_array['email']);
                        }
                    }
                    if ($logged_user['is_admin'] && isset($POST['role'])) {
                        $is_manager = 0;
                        $is_admin = 0;
                        switch ($POST['role']) {
                            case 'manager':
                                $is_manager = 1;

                                break;
                            case 'admin':
                                $is_admin = 1;

                                break;
                        }
                        if ($user['is_admin'] != $is_admin) {
                            $handler::setCond('admin', (bool) $is_admin);
                            $editing_array['is_admin'] = $is_admin;
                        }
                        if ($user['is_manager'] != $is_manager) {
                            $editing_array['is_manager'] = $is_manager;
                        }
                        if ($POST['role'] == 'admin') {
                            $editing_array['status'] = 'valid';
                        }
                        unset($POST['role']);
                    }
                    if (empty($POST['email'])) {
                        unset($editing_array['email']);
                    }
                    if (User::update($user['id'], $editing_array)) {
                        $user = array_merge($user, $editing_array);
                        $handler::updateVar('safe_post', [
                            'name' => safe_html($user['name']),
                        ]);
                    }

                    if (!$is_dashboard_user) {
                        $logged_user = User::getSingle($user['id']);
                    } else {
                        $user = User::getSingle($user['id'], 'id');
                    }
                    $changed_message = _s('Changes have been saved.');
                }
            }
            if ($doing == 'password') {
                if (Login::hasPassword($user['id'])) {
                    Login::deleteCookies('cookie', ['user_id' => $user['id']]);
                    Login::deleteCookies('session', ['user_id' => $user['id']]);
                    $is_changed = Login::changePassword((int) $user['id'], $POST['new-password']); // This inserts the session login
                    $changed_message = _s('Password has been changed');
                } else {
                    $is_changed = Login::addPassword((int) $user['id'], $POST['new-password']);
                    $changed_message = _s('Password has been created.');
                    if (!$is_dashboard_user || $logged_user['id'] == $user['id']) {
                        $logged_user = Login::login($user['id']);
                    }
                }
                if (!$is_dashboard_user) {
                    Login::insertCookie('cookie', $user['id']);
                }
                $unsets = ['current-password', 'new-password', 'new-password-confirm'];
                foreach ($unsets as $unset) {
                    $handler::updateVar('safe_post', [$unset => null]);
                }
            }
        } else {
            if (in_array($doing, ['', 'account']) && !$is_dashboard_user) {
                RequestLog::insert([
                    'type' => 'account-edit',
                    'result' => 'fail'
                ]);
                $error_message = _s('Wrong Username/Email values');
            }
        }
    }
    if ($doing == 'connections') {
        $connections = Login::getUserConnections($user['id']);
        $has_password = Login::hasPassword($user['id']);
        $handler::setCond('has_password', $has_password);
        $handler::setVar('connections', $connections);
        $handler::setVar('providers_enabled', $providersEnabled);
    }
    if ($doing === 'api') {
        if (!ApiKey::has(intval($user['id']))) {
            $apiCreated = ApiKey::insert(intval($user['id']));
            $handler::setVar('api_v1_key', $apiCreated);
        }
        $apiPub = ApiKey::getUserPublic(intval($user['id']));
        $handler::setVar('api_v1_public_display', $apiPub['public']);
        $handler::setVar('api_v1_date_created', $apiPub['date_gmt']);
    }
    $hasTwoFactor = TwoFactor::hasFor($user['id']);
    if ($doing === 'security' && !$hasTwoFactor) {
        $twoFactor = new TwoFactor();
        $twoFactorArgs = [
            'company' => Settings::get('website_name') . ' ' . env()['CHEVERETO_HOSTNAME'],
            'holder' => $user['username'] . '#' . $user['id_encoded'],
        ];
        $qrImage = $twoFactor->getQRCodeInline(...$twoFactorArgs);
        $handler::setVar('totp_qr_image', $qrImage);
        sessionVar()->put('two_factor_secret', $twoFactor->secret());
    }
    $pre_doctitle = [$routes[$doing]];
    $pre_doctitle[] = $is_dashboard_user
        ? _s('Settings for %s', $user['username'])
        : _s('Settings');
    $handler::setCond('two_factor_enabled', $hasTwoFactor);
    $handler::setCond('owner', $is_owner);
    $handler::setCond('error', $is_error);
    $handler::setCond('changed', $is_changed);
    $handler::setCond('dashboard_user', $is_dashboard_user);
    $handler::setCond('email_required', $is_email_required);
    $handler::setCond('captcha_needed', $captcha_needed);
    $handler::setVar('content_ip', $user['registration_ip']);
    $handler::setVar('pre_doctitle', implode(' - ', $pre_doctitle));
    $handler::setVar('error_message', $error_message);
    $handler::setVar('input_errors', $input_errors);
    $handler::setVar('changed_message', $changed_message ?? null);
    $handler::setVar('changed_email_message', $changed_email_message);
    $handler::setVar('user', $is_dashboard_user ? $user : $logged_user);
    $handler::setVar('safe_html_user', safe_html($handler::var('user')));
    if ($doing === 'account') {
        $bannedIp = IpBan::getSingle(['ip' => $handler::var('user')['registration_ip']]);
        $user_list_values = [
            [
                'label' => _s('Username'),
                'content' => '<a href="' . $handler::var('user')['url'] . '" class="btn btn-small default"><span class="icon fas fa-user-circle"></span><span class="margin-left-5">' . $handler::var('user')['username'] . '</span></a>' . (
                    $handler::cond('dashboard_user')
                        ? (' <a class="btn btn-small default" data-confirm="' . _s("Do you really want to delete this %s?", _n('user', 'users', 1)) . ' ' . _s("This can't be undone.") . '" data-submit-fn="CHV.fn.user.delete.submit" data-ajax-deferred="CHV.fn.complete_resource_delete" data-ajax-url="' . get_base_url("json") . '"><span class="icon fas fa-trash-alt"></span><span class="phone-hide margin-left-5">' . _s('Delete user') . '</span></a>')
                        : ''
                )
            ],
            [
                'label' => _s('User ID'),
                'content' => $handler::var('user')['id'] . ' (' . $handler::var('user')['id_encoded'] . ')'
            ],
            [
                'label' => _s('Images'),
                'content' => $handler::var('user')['image_count']
            ],
            [
                'label' => _n('Album', 'Albums', 20),
                'content' => $handler::var('user')['album_count']
            ],
            [
                'label' => _s('Register date'),
                'content' => $handler::var('user')['date']
            ],
            [
                'label' => '<span class="visibility-hidden">' . _s('Register date') . '</span>',
                'content' => $handler::var('user')['date_gmt'] . ' (GMT)'
            ]
        ];
        if ($handler::var('user')['registration_ip']) {
            $user_list_values[] = getIpButtonsArray($bannedIp, $handler::var('user')['registration_ip']);
        }
        $handler::setVar('user_list_values', $user_list_values);
    }
};
