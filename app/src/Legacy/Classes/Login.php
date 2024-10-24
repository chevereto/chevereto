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

use Chevereto\Config\Config;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use LogicException;
use Throwable;
use function Chevere\Message\message;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\check_hashed_token;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\is_valid_timezone;
use function Chevereto\Legacy\G\parse_user_agent;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\generate_hashed_token;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\cookie;
use function Chevereto\Vars\cookieVar;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

class Login
{
    public const ENCRYPTED_PROVIDER_NAMES = ['key_id', 'key_secret'];

    public const ENCRYPTED_CONNECTION_NAMES = ['token'];

    public const COOKIE = 'KEEP_LOGIN';

    protected static array $logged_user = [];

    protected static array $session = [];

    protected static array $providersPriorMacanudo = [
        'facebook' => [
            'label' => 'Facebook',
        ],
        'google' => [
            'label' => 'Google',
        ],
        'twitter' => [
            'label' => 'Twitter',
        ],
        'vk' => [
            'label' => 'VK',
        ],
    ];

    protected static array $cookies = [
        self::COOKIE => 'cookie',
        self::COOKIE . '_AMAZON' => 'cookie_amazon',
        self::COOKIE . '_APPLE' => 'cookie_apple',
        self::COOKIE . '_BITBUCKET' => 'cookie_bitbucket',
        self::COOKIE . '_DISCORD' => 'cookie_discord',
        self::COOKIE . '_DRIBBBLE' => 'cookie_dribbble',
        self::COOKIE . '_DROPBOX' => 'cookie_dropbox',
        self::COOKIE . '_FACEBOOK' => 'cookie_facebook',
        self::COOKIE . '_GITHUB' => 'cookie_github',
        self::COOKIE . '_GITLAB' => 'cookie_gitlab',
        self::COOKIE . '_GOOGLE' => 'cookie_google',
        self::COOKIE . '_INSTAGRAM' => 'cookie_instagram',
        self::COOKIE . '_LINKEDIN' => 'cookie_linkedin',
        self::COOKIE . '_MAILRU' => 'cookie_mailru',
        self::COOKIE . '_MEDIUM' => 'cookie_medium',
        self::COOKIE . '_ORCID' => 'cookie_orcid',
        self::COOKIE . '_ODNOKLASSNIKI' => 'cookie_odnoklassniki',
        self::COOKIE . '_QQ' => 'cookie_qq',
        self::COOKIE . '_REDDIT' => 'cookie_reddit',
        self::COOKIE . '_SPOTIFY' => 'cookie_spotify',
        // self::COOKIE . '_STACKEXCHANGE' => 'cookie_stackexchange',
        self::COOKIE . '_STEAM' => 'cookie_steam',
        self::COOKIE . '_STRAVA' => 'cookie_strava',
        self::COOKIE . '_TELEGRAM' => 'cookie_telegram',
        self::COOKIE . '_TUMBLR' => 'cookie_tumblr',
        self::COOKIE . '_TWITCHTV' => 'cookie_twitchtv',
        self::COOKIE . '_TWITTER' => 'cookie_twitter',
        self::COOKIE . '_VKONTAKTE' => 'cookie_vkontakte',
        self::COOKIE . '_WECHAT' => 'cookie_wechat',
        self::COOKIE . '_WORDPRESS' => 'cookie_wordpress',
        self::COOKIE . '_YAHOO' => 'cookie_yahoo',
        self::COOKIE . '_YANDEX' => 'cookie_yandex',
        //DeviantArt
        //Patreon
        //Paypal
        //Pinterest
        //Slack
    ];

    protected static bool $isPi;

    protected static bool $isMacanudo;

    public static function isPi(): bool
    {
        return self::$isPi
            ??= version_compare(cheveretoVersionInstalled(), '3.14.0.beta.1', '>=');
    }

    public static function isMacanudo(): bool
    {
        $version = cheveretoVersionInstalled();

        return self::$isMacanudo
            ??= (
                $version === ''
                    ? true
                    : version_compare($version, '4.0.0-beta.11', '>=')
            );
    }

    public static function getSocialCookieName(string $name): string
    {
        return array_flip(self::$cookies)['cookie_' . $name];
    }

    public static function tryLogin(): void
    {
        if (self::isPi()) {
            self::tryCookies();
        } else {
            try {
                $login = false;
                if (isset(cookie()['KEEP_LOGIN'])) {
                    $login = self::loginCookiePriorPi('internal');
                } elseif (isset(cookie()['KEEP_LOGIN_SOCIAL'])) {
                    $login = self::loginCookiePriorPi('social');
                }
                if ($login === false && isset(session()['login'])) {
                    $login = self::login(session()['login']['id']);
                }
            } catch (Throwable $e) {
                self::logoutPrePi();

                throw new Exception($e->getMessage(), 600, $e);
            }
        }
    }

    public static function addGuestContentToUser(array $user, int $id): void
    {
        if ($user === []) {
            return;
        }
        foreach (['albums', 'images'] as $table) {
            $sessionKey = 'guest_' . $table;
            if (! is_array(session()[$sessionKey] ?? null)) {
                continue;
            }

            try {
                $db = DB::getInstance();
                $getTable = DB::getTable($table);
                $fieldPrefix = DB::getFieldPrefix($table);
                $db->query('UPDATE ' . $getTable . ' SET ' . $fieldPrefix . '_user_id=' . $id . ' WHERE ' . $fieldPrefix . '_id IN (' . implode(',', session()[$sessionKey]) . ')');
                $db->exec();
                if ($db->rowCount() !== 0) {
                    DB::increment('users', [
                        $fieldPrefix . '_count' => '+' . $db->rowCount(),
                    ], [
                        'id' => $id,
                    ]);
                }
            } catch (Exception) {
            } // Silence
            sessionVar()->remove($sessionKey);
        }
    }

    public static function login(string|int $id, string $cookieType = 'cookie'): array
    {
        $id = (int) $id;
        $flip = array_flip(self::$cookies);
        if (! array_key_exists($cookieType, $flip)) {
            throw new Exception(sprintf('Invalid login $by %s', $cookieType), 600);
        }
        $user = User::getSingle($id, 'id');
        self::addGuestContentToUser($user, $id);
        RequestLog::delete([
            'user_id' => $id,
            'result' => 'fail',
            'type' => 'login',
            'ip' => get_client_ip(),
        ]);
        if ($user['status'] === 'valid') {
            self::unsetSignup();
            self::$session = [
                'user_id' => $id,
                'type' => $cookieType,
            ];
        } else {
            self::setSignup([
                'status' => $user['status'],
                'email' => $user['email'],
            ]);
        }
        if (isset(self::getUser()['timezone'])
            && self::getUser()['timezone'] !== Settings::get('default_timezone')
            && is_valid_timezone($user['timezone'] ?? '')
        ) {
            date_default_timezone_set($user['timezone']);
        }
        foreach (['image_count_label', 'album_count_label'] as $v) {
            $user[$v] = isset(self::$logged_user[$v]) ? _s(self::$logged_user[$v]) : '';
        }
        self::$logged_user = $user;

        return self::$logged_user;
    }

    public static function logout(): void
    {
        if (! self::isPi()) {
            self::logoutPrePi();
        }
        self::$logged_user = [];
        self::$session = [];
        self::unsetSignup();
        foreach (array_keys(self::$cookies) as $name) {
            $validate = self::validateCookie($name);
            if ($validate['valid']) {
                DB::delete('login_cookies', [
                    'id' => $validate['id'],
                ]);

                continue;
            }

            try {
                static::unsetCookie($name);
            } catch (Throwable) {
            }
        }
    }

    public static function insertCookie(string $type, string|int $userId): int
    {
        $values = [
            'user_id' => $userId,
        ];
        if (! self::isMacanudo()) {
            return self::insertPriorMacanudo($type, $values);
        }
        self::assertNoSessionType($type);
        $values['ip'] = get_client_ip();
        $values['date_gmt'] = datetimegmt();
        $values['user_agent'] = self::getUserAgent();

        return self::putCookie($type, $values);
    }

    public static function redirectToAfterCookie(int $userId, string $redirect): string
    {
        if (TwoFactor::hasFor($userId)) {
            sessionVar()->put('challenge_two_factor', $userId);
            $redirect = 'account/two-factor';
        }

        return $redirect;
    }

    public static function insertConnection(string $provider, array $values): int
    {
        self::assertEnabledProvider($provider);
        if (! self::isMacanudo()) {
            return self::insertPriorMacanudo($provider, $values);
        }
        self::assertArrayWithKeys($values, ['user_id', 'resource_id', 'resource_name', 'token']);
        if (! isset($values['date_gmt'])) {
            $values['date_gmt'] = datetimegmt();
        }
        if (is_array($values['token'])) {
            $values['token'] = serialize($values['token']);
        }
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_CONNECTION_NAMES, $values);
        }
        $query = <<<SQL
        INSERT INTO `%table_prefix%login_connections` (`login_connection_provider_id`,
                                                    `login_connection_user_id`,
                                                    `login_connection_date_gmt`,
                                                    `login_connection_resource_id`,
                                                    `login_connection_resource_name`,
                                                    `login_connection_token`)
        SELECT (
                SELECT login_provider_id FROM `%table_prefix%login_providers` WHERE login_provider_name = :provider
            ),
            :user_id,
            :date_gmt,
            :resource_id,
            :resource_name,
            :token;
        SQL;

        return DB::preparedQueryExecute($query, [
            ':provider' => $provider,
            ':user_id' => $values['user_id'],
            ':date_gmt' => $values['date_gmt'],
            ':resource_id' => $values['resource_id'],
            ':resource_name' => $values['resource_name'],
            ':token' => $values['token'],
        ]);
    }

    public static function getCookie(string $type, array $values): array
    {
        if (! starts_with('cookie', $type)) {
            throw new InvalidArgumentException(
                message('Type `%t` is not supported', t: $type)
            );
        }
        $provider = self::getProviderFromCookieType($type);
        self::assertArrayWithKeys($values, ['user_id', 'date_gmt']);
        if (! self::isMacanudo()) {
            if ($provider !== '') {
                self::assertEnabledProvider($provider);
            }
            $values['type'] = $type;
            $get = self::getPriorMacanudo(values: $values, limit: 1);

            return [
                'id' => (int) ($get['id'] ?? 0),
                'user_id' => (int) $values['user_id'],
                'hash' => ($get['secret'] ?? '')
                    . ($get['token_hash'] ?? ''),
            ];
        }
        if ($type === 'cookie') {
            $values['connection_id'] = 0;
            $get = DB::get(table: 'login_cookies', where: $values, limit: 1);
            $get = DB::formatRows($get, 'login_cookie');
        } else {
            $query = <<<SQL
            SELECT login_cookie_id id, login_cookie_hash hash
            FROM `%table_prefix%login_cookies`
                    JOIN `%table_prefix%login_connections` ON login_cookie_connection_id = login_connection_id
                    JOIN `%table_prefix%login_providers` ON login_connection_provider_id = login_provider_id
            WHERE login_cookie_user_id = :user_id
            AND login_cookie_date_gmt = :date_gmt
            AND login_provider_name = :name
            AND login_provider_is_enabled = 1
            LIMIT 1;
            SQL;
            $get = DB::fetchSingleQuery($query, [
                ':user_id' => (int) $values['user_id'],
                ':date_gmt' => $values['date_gmt'],
                ':name' => $provider,
            ]);
        }
        if (! $get) {
            return [
                'id' => 0,
                'user_id' => 0,
                'hash' => '',
            ];
        }

        return [
            'id' => (int) ($get['id']),
            'user_id' => (int) $values['user_id'],
            'hash' => $get['hash'],
        ];
    }

    public static function getUserIdForResource(string $type, int|string $resourceId): int
    {
        // if (! self::isMacanudo()) {
        //     $get = self::getPriorMacanudo(
        //         values: [
        //             'resource_id' => $resourceId,
        //             'type' => $type,
        //         ],
        //         sort: [
        //             'field' => 'date_gmt',
        //             'order' => 'desc',
        //         ],
        //         limit: 1
        //     );
        // }
        $query = <<<SQL
        SELECT login_connection_user_id user_id
        FROM `%table_prefix%login_connections`
            JOIN `%table_prefix%login_providers` ON login_connection_provider_id = login_provider_id
            JOIN `%table_prefix%users` ON login_connection_user_id = user_id
        WHERE login_connection_resource_id = :resource_id
        AND login_provider_name = :name
        ORDER BY login_connection_date_gmt DESC
        LIMIT 1;
        SQL;
        $get = DB::fetchSingleQuery($query, [
            ':resource_id' => $resourceId,
            ':name' => $type,
        ]);

        return $get['user_id'] ?? 0;
    }

    public static function getUserConnections(int $userId): array
    {
        $connections = [];
        if (self::isMacanudo()) {
            $query = <<<SQL
            SELECT login_provider_name name, login_provider_label label, login_connection_id id, login_connection_resource_id resource_id, login_connection_resource_name resource_name
            FROM `%table_prefix%login_connections`
                    JOIN `%table_prefix%login_providers` ON login_connection_provider_id = login_provider_id
            WHERE login_connection_user_id = :user_id
            AND login_provider_is_enabled = 1
            ORDER BY login_provider_name DESC;
            SQL;
            $fetchAll = DB::fetchAllQuery($query, [
                ':user_id' => $userId,
            ]);
            foreach ($fetchAll as &$connection) {
                $connections[$connection['name']] = $connection;
            }
        } else {
            $logins = self::getPriorMacanudo([
                'user_id' => $userId,
            ]);
            $providersEnabled = self::getProviders('enabled');
            foreach ($logins as $login) {
                if (! array_key_exists($login['type'], $providersEnabled)) {
                    continue;
                }
                $connections[$login['type']] = $login;
                $connections[$login['type']]['label'] = $providersEnabled[$login['type']];
            }
            ksort($connections);
        }

        return $connections;
    }

    public static function deleteCookies(string $type, array $values): int
    {
        if (! self::isMacanudo()) {
            $values['type'] = $type;

            return DB::delete('logins', $values);
        }
        if ($type === 'session') {
            return 0;
        }
        if ($type !== 'cookie') {
            $provider = str_replace_first('cookie_', '', $type);
            self::assertProvider($provider);
            self::assertArrayWithKeys($values, ['user_id']);
            $query = <<<SQL
            DELETE `%table_prefix%login_cookies`
            FROM `%table_prefix%login_cookies`
                    JOIN `%table_prefix%login_connections` ON login_cookie_connection_id = login_connection_id
                    JOIN `%table_prefix%login_providers` ON login_connection_provider_id = login_provider_id
            WHERE login_cookie_user_id = :user_id
            AND login_provider_name = :provider_name;
            SQL;

            return DB::preparedQueryExecute($query, [
                ':user_id' => $values['user_id'],
                ':provider_name' => $provider,
            ]);
        }
        $values['connection_id'] = 0;

        return DB::delete('login_cookies', $values);
    }

    public static function getConnection(int $id): array
    {
        $query = <<<SQL
            SELECT login_provider_name name, login_provider_label label, login_connection_id id, login_connection_resource_id resource_id, login_connection_resource_name resource_name, login_connection_token token
            FROM `%table_prefix%login_connections`
                    JOIN `%table_prefix%login_providers` ON login_connection_provider_id = login_provider_id
            WHERE login_connection_id = :id;
            SQL;
        $fetchSingle = DB::fetchSingleQuery($query, [
            ':id' => $id,
        ]);
        if (hasEncryption()) {
            $fetchSingle = decryptValues(self::ENCRYPTED_CONNECTION_NAMES, $fetchSingle);
        }
        $fetchSingle['token'] = unserialize($fetchSingle['token'] ?? 'a:0:{}') ?: [];

        return $fetchSingle;
    }

    public static function updateConnection(int $id, array $values): int
    {
        if (is_array($values['token'])) {
            $values['token'] = serialize($values['token']);
        }
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_CONNECTION_NAMES, $values);
        }

        return DB::update(
            table: 'login_connections',
            values: $values,
            wheres: [
                'id' => $id,
            ]
        );
    }

    public static function deleteConnection(string $provider, int|string $userId): int
    {
        self::assertProvider($provider);
        if (! self::isMacanudo()) {
            return DB::delete('logins', [
                'type' => $provider,
            ]);
        }
        $query = <<<SQL
        DELETE `%table_prefix%login_connections`
        FROM `%table_prefix%login_connections`
                JOIN `%table_prefix%login_providers` ON login_provider_id = login_connection_provider_id
        WHERE login_connection_user_id = :user_id
        AND login_provider_name = :provider_name;
        SQL;

        return DB::preparedQueryExecute($query, [
            ':user_id' => strval($userId),
            ':provider_name' => $provider,
        ]);
    }

    public static function hasSignup(): bool
    {
        return isset(session()['signup']) && session()['signup'] !== [];
    }

    public static function getSignup(): array
    {
        return session()['signup'];
    }

    public static function setSignup(array $var): void
    {
        sessionVar()->put('signup', $var);
    }

    public static function unsetSignup(): void
    {
        if (isset(session()['signup'])) {
            sessionVar()->remove('signup');
        }
    }

    public static function hasSession(): bool
    {
        return self::$session !== [];
    }

    public static function getSession(): array
    {
        return self::$session;
    }

    public static function getUser(): array
    {
        return self::$logged_user;
    }

    public static function setUser(string $key, mixed $value)
    {
        if (self::$logged_user !== []) {
            self::$logged_user[$key] = $value;
        }
    }

    public static function isLoggedUser(): bool
    {
        return self::$logged_user !== [];
    }

    /**
     * @return array
     */
    public static function validateCookie(string $cookieName)
    {
        if (! isset(cookie()[$cookieName])) {
            return [
                'valid' => false,
            ];
        }
        $fetchCookie = static::fetchCookie($cookieName);
        if ($fetchCookie === []) {
            return [
                'valid' => false,
                'cookie' => [],
                'id' => null,
                'user_id' => 0,
            ];
        }
        /**
         * $fetchCookie = [
         *  'raw' => 'asdf',
         *  'user_id' => $user_id,
         *  'type' => $type,
         *  'date_gmt' => $date_gmt,]
         */
        $login_arr = $fetchCookie;
        unset($login_arr['raw'], $login_arr['type']);
        /**
         * $login_arr = [
         *  'user_id' => $user_id,
         *  'date_gmt' => $date_gmt,]
         */
        $getCookie = self::getCookie(
            type: $fetchCookie['type'],
            values: $login_arr,
        );
        $is_valid = check_hashed_token(
            $getCookie['hash'] ?? '',
            $fetchCookie['raw']
        );

        return [
            'valid' => $is_valid,
            'cookie' => $fetchCookie,
            'id' => $getCookie['id'] ?? null,
            'user_id' => $fetchCookie['user_id'],
        ];
    }

    public static function hasPassword(int $userId): bool
    {
        if (self::isMacanudo()) {
            $get = DB::get(
                table: 'login_passwords',
                where: [
                    'user_id' => $userId,
                ],
                limit: 1
            );
        } else {
            $get = DB::get(
                table: 'logins',
                where: [
                    'user_id' => $userId,
                    'type' => 'password',
                ],
                limit: 1
            );
        }

        return (bool) $get;
    }

    public static function checkPassword(int $userId, string $tryPassword): bool
    {
        if (self::isMacanudo()) {
            $get = DB::get(
                table: 'login_passwords',
                where: [
                    'user_id' => $userId,
                ],
                limit: 1
            );
        } else {
            $get = DB::get(
                table: 'logins',
                where: [
                    'user_id' => $userId,
                    'type' => 'password',
                ],
                limit: 1
            );
        }
        if (! $get) {
            return false;
        }

        return password_verify($tryPassword, $get['login_password_hash'] ?? $get['login_secret']);
    }

    public static function addPassword(
        int $userId,
        string $password,
        bool $update_session = true
    ): bool {
        return self::passwordData('insert', $userId, $password, $update_session);
    }

    public static function changePassword(
        int $userId,
        string $password,
        bool $update_session = true
    ): bool {
        return self::passwordData('update', $userId, $password, $update_session);
    }

    public static function updateProvider(string $provider, array $values): int
    {
        if (hasEncryption()) {
            $values = encryptValues(self::ENCRYPTED_PROVIDER_NAMES, $values);
        }

        return DB::update(
            table: 'login_providers',
            values: $values,
            wheres: [
                'name' => $provider,
            ]
        );
    }

    public static function getProviders(string $get = 'all'): array
    {
        $return = [];
        if (! self::isMacanudo()) {
            return $get === 'all'
                ? self::$providersPriorMacanudo
                : self::getProvidersPriorMacanudo($get);
        }
        $binds = [];
        $query =
            <<<SQL
            SELECT login_provider_name AS name,
                   login_provider_label AS label,
                   login_provider_key_id AS key_id,
                   login_provider_key_secret AS key_secret,
                   login_provider_is_enabled AS is_enabled
            FROM `%table_prefix%login_providers`%wheres%
            ORDER BY name ASC;
            SQL;
        if ($get !== 'all') {
            $query = str_replace(
                '%wheres%',
                <<<SQL

                WHERE login_provider_is_enabled = :is_enabled
                SQL,
                $query
            );
            $binds[':is_enabled'] = (int) ($get === 'enabled');
        } else {
            $query = str_replace('%wheres%', '', $query);
        }
        $fetch = DB::fetchAllQuery($query, $binds);
        foreach ($fetch as $row) {
            $name = $row['name'];
            unset($row['name']);
            $row['is_enabled'] = (bool) $row['is_enabled'];
            if (hasEncryption()) {
                $row = decryptValues(self::ENCRYPTED_PROVIDER_NAMES, $row);
            }
            $return[$name] = $row;
        }

        return $return;
    }

    public static function isAdmin(): bool
    {
        if (self::$logged_user === []) {
            return false;
        }

        return (bool) self::$logged_user['is_admin'];
    }

    public static function isManager(): bool
    {
        if (self::$logged_user === []) {
            return false;
        }

        return (bool) self::$logged_user['is_manager'];
    }

    public static function unsetCookie(string $key): bool
    {
        return static::cookie($key, '', -1);
    }

    protected static function assertNoSessionType(string $type): void
    {
        if ($type === 'session') {
            throw new LogicException(
                message('Type `%t` is not supported', t: $type),
                600
            );
        }
    }

    protected static function getUserAgent(): string
    {
        return json_encode(array_merge(parse_user_agent(server()['HTTP_USER_AGENT'])));
    }

    protected static function putCookie(string $type, array $values): int
    {
        $table = 'login_cookies';
        $hashColumn = 'hash';
        if (self::isMacanudo()) {
            $values['connection_id'] = 0;
        } else {
            $hashColumn = 'secret';
            $table = 'logins';
            $values['type'] = $type;
        }
        $tokenize = generate_hashed_token((int) $values['user_id']);
        $values[$hashColumn] = $tokenize['hash'];
        $cookieName = self::COOKIE;
        $provider = self::getProviderFromCookieType($type);
        if ($provider !== '') {
            self::assertEnabledProvider($provider);
            $cookieName .= '_' . str_replace_first('COOKIE_', '', strtoupper($provider));
        }
        if (self::isMacanudo() && $type !== 'cookie') {
            $query = <<<SQL
            INSERT INTO `%table_prefix%login_cookies` (login_cookie_connection_id, login_cookie_user_id, login_cookie_date_gmt,
                                                       login_cookie_ip, login_cookie_user_agent, login_cookie_hash)
            SELECT (
                    SELECT login_connection_id
                    FROM `%table_prefix%login_connections`
                                JOIN %table_prefix%login_providers ON login_provider_id = login_connection_provider_id
                    WHERE login_provider_name = :provider
                    AND login_connection_user_id = :user_id
                ),
                :user_id,
                :date_gmt,
                :ip,
                :user_agent,
                :hash;
            SQL;
            $insert = DB::preparedQueryExecute($query, [
                ':provider' => $provider,
                ':user_id' => $values['user_id'],
                ':date_gmt' => $values['date_gmt'],
                ':ip' => $values['ip'],
                ':user_agent' => $values['user_agent'],
                ':hash' => $tokenize['hash'],
            ]);
        } else {
            $insert = DB::insert($table, $values);
        }
        if ($insert !== 0) {
            $dateTime = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $values['date_gmt'],
                new DateTimeZone('UTC')
            );
            $cookie = $tokenize['public_token_format']
                . ':'
                . $dateTime->getTimestamp();
            static::setCookie($cookieName, $cookie);
        }

        return $insert;
    }

    protected static function insertPriorMacanudo(string $type, array $values): int
    {
        if (! isset($values['ip'])) {
            $values['ip'] = get_client_ip();
        }
        if (! isset($values['hostname'])) {
            $values['hostname'] = self::getUserAgent();
        }
        if (! isset($values['date'])) {
            $values['date'] = datetime();
        }
        if (! isset($values['date_gmt'])) {
            $values['date_gmt'] = datetimegmt();
        }
        if (starts_with('cookie', $type)) {
            return self::putCookie($type, $values);
        }

        return DB::insert('logins', $values);
    }

    protected static function getProviderFromCookieType(string $cookieType): string
    {
        $provider = '';
        if ($cookieType !== 'cookie') {
            $provider = str_replace_first('cookie_', '', $cookieType);
        }

        return $provider;
    }

    protected static function assertEnabledProvider(string $provider): void
    {
        $get = self::isMacanudo()
            ? DB::get(
                table: 'login_providers',
                where: [
                    'name' => $provider,
                    'is_enabled' => 1,
                ],
                limit: 1
            )
            : (bool) getSetting($provider);
        if (! $get) {
            throw new InvalidArgumentException(
                message('Provider `%t` is not enabled', $provider)
            );
        }
    }

    protected static function assertArrayWithKeys(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                throw new InvalidArgumentException(
                    message('Key `%t` is missing', $key)
                );
            }
        }
    }

    protected static function getPriorMacanudo(array $values, array $sort = [], ?int $limit = null): array
    {
        $get = DB::get('logins', $values, 'AND', $sort, $limit);
        if (! $get) {
            return [];
        }

        return DB::formatRows($get, 'login');
    }

    protected static function assertProvider(string $provider): void
    {
        $get = DB::get(
            table: 'login_providers',
            where: [
                'name' => $provider,
            ],
            limit: 1
        );
        if (! $get) {
            throw new InvalidArgumentException(
                message('Invalid login provider `%s`', s: $provider)
            );
        }
    }

    protected static function getProvidersPriorMacanudo(string $get = 'enabled'): array
    {
        $return = [];
        foreach (self::$providersPriorMacanudo as $name => $provider) {
            if ($get === 'enabled' && ! getSetting($name)
                || $get === 'disabled' && getSetting($name)
            ) {
                continue;
            }
            $return[$name] = $provider;
        }

        return $return;
    }

    /**
     * @return null|array|false Null if no cookies, array if cookie+login, false if cookie+error
     */
    protected static function tryCookies(): array|bool|null
    {
        $login = null;
        foreach (array_keys(self::$cookies) as $cookieName) {
            if (! array_key_exists($cookieName, cookie())) {
                continue;
            }
            $loginCookie = self::loginCookie($cookieName);
            if ($loginCookie !== []) {
                $login = $loginCookie;

                break;
            }
        }

        return $login;
    }

    /**
     * @return array logged user if any
     */
    protected static function loginCookie(string $cookieName = self::COOKIE): array
    {
        if (! array_key_exists($cookieName, self::$cookies)) {
            return [];
        }
        $validate = self::validateCookie($cookieName);
        if ($validate['valid']) {
            self::login($validate['user_id'], $validate['cookie']['type']);
            self::$session['id'] = $validate['id'];
            self::$session['login_cookies'][] = $validate['id'];

            return self::$logged_user;
        }
        RequestLog::insert([
            'result' => 'fail',
            'type' => 'login',
            'user_id' => $validate['user_id'],
        ]);
        static::unsetCookie($cookieName);

        return [];
    }

    protected static function loginCookiePriorPi(string $type = 'internal'): array|bool|null
    {
        if (! in_array($type, ['internal', 'social'], true)) {
            throw new Exception('Invalid login type');
        }
        $cookie = cookie()[$type === 'internal' ? 'KEEP_LOGIN' : 'KEEP_LOGIN_SOCIAL'];
        $explode = array_filter(explode(':', $cookie));
        // CHV: 0->id | 1:token | 2:timestamp
        // SOC: 0->id | 1:type | 2:hash | 3:timestamp
        $count = $type === 'social' ? 4 : 3;
        if (count($explode) !== $count) {
            return false;
        }
        $user_id = decodeID($explode[0]);
        $login_db_arr = [
            'user_id' => $user_id,
            'date_gmt' => gmdate('Y-m-d H:i:s', (int) end($explode)),
        ];
        $getCookie = self::getCookie(
            type: $type === 'internal'
                ? 'cookie'
                : $explode[1],
            values: $login_db_arr,
        );
        $is_valid_token = $type === 'internal'
            ? check_hashed_token($getCookie['hash'], $cookie)
            : password_verify($getCookie['hash'], $explode[2]);
        if ($is_valid_token) {
            return self::login(
                $getCookie['user_id'],
                $type === 'internal'
                    ? 'cookie'
                    : $explode[1]
            );
        }
        RequestLog::insert(
            [
                'result' => 'fail',
                'type' => 'login',
                'user_id' => $user_id,
            ]
        );
        self::logoutPrePi();

        return null;
    }

    protected static function logoutPrePi(): void
    {
        self::$logged_user = [];
        $doing = session()['login']['type'];
        if ($doing === 'session') {
            self::deleteCookies('session', [
                'user_id' => session()['login']['id'],
                'date_gmt' => session()['login']['datetime'],
            ]);
        }
        session_unset();
        $cookies = ['KEEP_LOGIN', 'KEEP_LOGIN_SOCIAL'];
        foreach ($cookies as $cookie_name) {
            static::unsetCookie($cookie_name);
            if ($cookie_name === 'KEEP_LOGIN_SOCIAL') {
                continue;
            }
            $cookie = cookie()[$cookie_name];
            $explode = array_filter(explode(':', $cookie));
            if (count($explode) === 4) {
                $user_id = decodeID($explode[0]);
                self::deleteCookies('cookie', [
                    'user_id' => $user_id,
                    'date_gmt' => gmdate('Y-m-d H:i:s', (int) $explode[3]),
                ]);
            }
        }
    }

    protected static function setCookie(string $key, string $value): bool
    {
        return static::cookie(
            $key,
            $value,
            time() + (60 * 60 * 24 * 30)
        );
    }

    protected static function cookie(string $key, string $value, int $time): bool
    {
        if ($time === -1) {
            cookieVar()->remove($key);
        } else {
            cookieVar()->put($key, $value);
        }
        $args = func_get_args();
        $args[] = Config::host()->hostnamePath();
        if ($time === -1) {
            // PrePi
            setcookie(...$args);
        }
        $args[] = Config::host()->hostname();
        $args[] = HTTP_APP_PROTOCOL === 'https'; // @phpstan-ignore-line
        $args[] = true;

        return setcookie(...$args);
    }

    protected static function fetchCookie(string $cookieName): array
    {
        $rawCookie = cookie()[$cookieName];
        $explode = array_filter(
            explode(':', $rawCookie)
        );
        if (count($explode) !== 3) {
            return [];
        }

        return [
            'raw' => $rawCookie,
            'user_id' => decodeID($explode[0]),
            'type' => self::$cookies[$cookieName],
            'date_gmt' => gmdate('Y-m-d H:i:s', (int) $explode[2]),
        ];
    }

    protected static function passwordData(
        string $action,
        int $userId,
        string $password,
        bool $updateSession
    ): bool {
        $action = strtoupper($action);
        if (! in_array($action, ['UPDATE', 'INSERT'], true)) {
            throw new Exception('Expecting UPDATE or INSERT statements');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $wheres = [
            'user_id' => $userId,
        ];
        if (self::isMacanudo()) {
            $table = 'login_passwords';
            $values = [
                'date_gmt' => datetimegmt(),
                'hash' => $hash,
            ];
        } else {
            $table = 'logins';
            $wheres['type'] = 'password';
            $values = [
                'ip' => get_client_ip(),
                'date' => datetime(),
                'date_gmt' => datetimegmt(),
                'secret' => $hash,
            ];
        }
        if ($action === 'UPDATE') {
            $db = DB::update($table, $values, $wheres);
            static::deleteCookies('cookie', [
                'user_id' => $userId,
            ]);
        } else {
            $values['user_id'] = $userId;
            if (! self::isMacanudo()) {
                $values['type'] = 'password';
            }
            $db = DB::insert($table, $values);
        }
        if (self::isLoggedUser()
            && self::getUser()['id'] === $userId
            && self::hasSession()
            && $updateSession) {
            self::$session = [
                'id' => $userId,
                'type' => 'password',
            ];
        }

        return (bool) $db;
    }
}
