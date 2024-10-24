<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\HybridauthSession;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use Hybridauth\Exception\InvalidAuthorizationStateException;
use Hybridauth\Hybridauth;
use function Chevere\Message\message;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\getSetting;

return function (Handler $handler) {
    if (! version_compare(cheveretoVersionInstalled(), '4.0.0-beta.11', '>=')) {
        echo 'Route not available until the system update gets installed.';
        exit();
    }
    $doing = $handler->request()[0] ?? '';
    $providersEnabled = Login::getProviders('enabled');
    $doable = array_keys($providersEnabled);
    if (! in_array($doing, $doable)) {
        $handler->issueError(404);

        return;
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    $cookieName = Login::getSocialCookieName($doing);
    if ($logged_user !== []) {
        $validate = Login::validateCookie($cookieName);
        if ($validate['valid']
            && in_array($validate['id'], Login::getSession()['login_cookies'] ?? [])
        ) {
            redirect('settings/connections#' . $doing, 302);

            return;
        }
    }
    $config = [
        'callback' => get_public_url('connect/' . $doing) . '/',
        'providers' => [],
    ];
    foreach ($providersEnabled as $name => $provider) {
        $config['providers'][$name] = [
            'enabled' => $provider['is_enabled'],
            'keys' => [
                'id' => $provider['key_id'],
                'secret' => $provider['key_secret'],
            ],
        ];
    }
    $session = new HybridauthSession();
    $hybridauth = new Hybridauth(config: $config, storage: $session);
    $adapter = $hybridauth->getAdapter($doing);

    try {
        if (! $adapter->isConnected()) {
            $adapter->authenticate();
        }
    } catch (InvalidAuthorizationStateException) {
        $session->clear();
        redirect('connect/' . $doing, 302);
    }
    if ($adapter->isConnected()) {
        $user = $logged_user;
        $connectProfile = $adapter->getUserProfile();
        $connectedUserId = Login::getUserIdForResource(
            $doing,
            $connectProfile->identifier
        );
        if ($connectedUserId !== 0) {
            if ($user === []) {
                $user = User::getSingle($connectedUserId);
            }
            if ($connectedUserId != ($user['id'] ?? 0)) {
                Login::logout();
                redirect('connect/' . $doing, 302);
            }
        }
        if ($user === []) {
            if (! Settings::get('enable_signups')) {
                $handler->issueError(403);

                return;
            }
            $username = '';
            preg_match_all('/[\w]/', $connectProfile->displayName, $user_matches);
            foreach ($user_matches[0] as $match) {
                $username .= $match;
            }
            $baseUsername = substr(strtolower($username), 0, (int) Settings::USERNAME_MAX_LENGTH);
            $username = $baseUsername;
            $j = 1;
            while (! User::isValidUsername($username)) {
                if (strlen($username) > Settings::USERNAME_MAX_LENGTH) {
                    $username = substr($baseUsername, 0, -strlen(strval($j))) . $j;
                } else {
                    $username .= $j;
                }
                $j++;
            }
            $i = 1;
            while (User::getSingle($username, 'username', false)) {
                if (strlen($username) > Settings::USERNAME_MAX_LENGTH) {
                    $username = substr($baseUsername, 0, -strlen(strval($i))) . $i;
                } else {
                    $username .= $i;
                }
                $i++;
            }
            $insert_user_values = [
                'username' => $username,
                'name' => $connectProfile->displayName,
                'status' => getSetting('require_user_email_social_signup')
                    ? 'awaiting-email'
                    : 'valid',
                'website' => $connectProfile->webSiteURL,
                'timezone' => getSetting('default_timezone'),
                'language' => L10n::getLocale(),
            ];
            $insert_user_values = array_filter($insert_user_values);
            $inserted_user = User::insert($insert_user_values);
            $user = User::getSingle($inserted_user, 'id', true);
        }
        if ($user === []) {
            throw new LogicException(message('User not found'));
        }
        $values = [
            'user_id' => $user['id'],
            'resource_id' => $connectProfile->identifier,
            'resource_name' => $connectProfile->displayName,
            'token' => $adapter->getAccessToken(),
        ];
        $connection = Login::getUserConnections((int) $user['id'])[$doing] ?? null;
        if ($connection !== null) {
            Login::updateConnection((int) $connection['id'], $values);
        } else {
            Login::insertConnection($doing, $values);
        }
        Login::insertCookie('cookie_' . $doing, $user['id']);
        $adapter->disconnect();

        // if (isset($connectProfile->photoURL) && !isset($user['avatar']['filename'])) {
        //     try {
        //         User::uploadPicture($user, 'avatar', $connectProfile->photoURL);
        //     } catch (Throwable) {
        //     }
        // }

        $redirectTo = Login::redirectToAfterCookie(
            $user['id'],
            $logged_user === []
                ? $user['url']
                : 'settings/connections#' . $doing
        );
        redirect($redirectTo, 302);
    }

    redirect('', 302);

    exit();
};
