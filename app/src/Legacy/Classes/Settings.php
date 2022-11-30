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

use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\LogicException;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\G\get_regex_match;
use function Chevereto\Legacy\G\is_integer;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Vars\env;
use Exception;

class Settings
{
    protected static ?self $instance;

    private static array $settings = [];

    private static array $defaults = [];

    private static array $typeset = [];

    private static array $decrypted = [];

    public const ENCRYPTED_NAMES = [
        'api_v1_key',
        'email_smtp_server',
        'email_smtp_server_password',
        'email_smtp_server_port',
        'email_smtp_server_username',
        'captcha_secret',
        'disqus_secret_key',
        'akismet_api_key',
        'moderatecontent_key',
        'arachnid_key',
        'xr_host',
        'xr_port',
        'xr_key',
    ];

    public function __construct()
    {
        $settings = [];
        $defaults = [];
        $typeset = [];

        try {
            $db_settings = DB::get(
                table: 'settings',
                values: 'all',
                sort: ['field' => 'name', 'order' => 'asc']
            );
            foreach ($db_settings as $k => $v) {
                $v = DB::formatRow($v);
                $value = $v['value'];
                $default = $v['default'];
                if ($v['typeset'] == 'bool') {
                    $value = $value == 1;
                    $default = $default == 1;
                }
                if ($v['typeset'] == 'string') {
                    $value = (string) $value;
                    $default = (string) $default;
                }
                $typeset[$v['name']] = $v['typeset'];
                $settings[$v['name']] = $value;
                $defaults[$v['name']] = $default;
            }
        } catch (Exception $e) {
            $settings = [];
            $defaults = [];
        }
        $stock = [
            'default_language' => 'en',
            'auto_language' => true,
            'theme_download_button' => true,
            'enable_signups' => true,
            'website_mode' => 'community',
            'listing_pagination_mode' => 'classic',
            'website_content_privacy_mode' => 'default',
            'website_privacy_mode' => 'public',
            'website_explore_page' => true,
            'website_search' => true,
            'website_random' => true,
            'theme_show_social_share' => true,
            'theme_show_embed_uploader' => true,
            'user_routing' => true,
            'require_user_email_confirmation' => true,
            'require_user_email_social_signup' => true,
            'homepage_style' => 'landing',
            'user_image_avatar_max_filesize_mb' => '1',
            'user_image_background_max_filesize_mb' => '2',
            'theme_image_right_click' => false,
            'theme_show_exif_data' => true,
            'homepage_cta_color' => 'green',
            'homepage_cta_outline' => false,
            'watermark_enable_guest' => true,
            'watermark_enable_user' => true,
            'watermark_enable_admin' => true,
            'language_chooser_enable' => true,
            'languages_disable' => null,
            'homepage_cta_fn' => 'cta-upload',
            'watermark_target_min_width' => 100,
            'watermark_target_min_height' => 100,
            'watermark_percentage' => 4,
            'watermark_enable_file_gif' => false,
            'upload_medium_fixed_dimension' => 'width',
            'upload_medium_size' => 500,
            'enable_followers' => true,
            'enable_likes' => true,
            'enable_consent_screen' => false,
            'user_minimum_age' => null,
            'route_image' => 'image',
            'route_album' => 'album',
            'enable_duplicate_uploads' => false,
            'upload_threads' => '2',
            'enable_automatic_updates_check' => true,
            'comments_api' => 'js',
            'image_load_max_filesize_mb' => '3',
            'upload_max_image_width' => '0',
            'upload_max_image_height' => '0',
            'enable_expirable_uploads' => null,
            'enable_user_content_delete' => false,
            'enable_plugin_route' => true,
            'sdk_pup_url' => null,
            'website_explore_page_guest' => true,
            'explore_albums_min_image_count' => 5,
            'upload_max_filesize_mb_guest' => 0.5,
            'notify_user_signups' => false,
            'listing_viewer' => true,
            'seo_image_urls' => true,
            'seo_album_urls' => true,
            // 'website_https' => 'auto',
            'upload_gui' => 'js',
            'captcha_api' => 'hcaptcha',
            'force_captcha_contact_page' => true,
            'dump_update_query' => false,
            'enable_powered_by' => true,
            'akismet' => false,
            'stopforumspam' => false,
            'upload_enabled_image_formats' => 'jpg,png,bmp,gif,webp',
            'hostname' => null,
            'theme_show_embed_content_for' => 'all',
            'moderatecontent' => false,
            'moderatecontent_key' => '',
            'moderatecontent_block_rating' => 'a',
            'moderatecontent_flag_nsfw' => 'a',
            'moderate_uploads' => '',
            'image_lock_nsfw_editing' => false,
            'enable_uploads_url' => false,
            'chevereto_news' => 'a:0:{}',
            'cron_last_ran' => '0000-00-00 00:00:00',
            'logo_type' => 'vector',
            'theme_palette' => '0',
            'enable_xr' => false,
            'xr_host' => 'localhost',
            'xr_port' => '27420',
            'xr_key' => '',
            'route_user' => 'user',
            'root_route' => 'user',
            'arachnid' => false,
            'arachnid_key' => '',
            'image_first_tab' => 'info',
            'website_random_guest' => true,
            'website_search_guest' => true,
            'debug_errors' => false,
        ];
        if (env()['CHEVERETO_SERVICING'] === 'docker') {
            $stock['xr_host'] = 'host.docker.internal';
        }
        $device_to_columns = [
            'phone' => 1,
            'phablet' => 3,
            'tablet' => 4,
            'laptop' => 5,
            'desktop' => 6,
        ];
        foreach ($device_to_columns as $k => $v) {
            $stock['listing_columns_' . $k] = $v;
        }
        foreach ($stock as $k => $v) {
            if (!array_key_exists($k, $settings)) {
                $settings[$k] = $v;
                $defaults[$k] = $v;
            }
        }
        if (isset($settings['email_mode']) && $settings['email_mode'] == 'phpmail') {
            $settings['email_mode'] = 'mail';
        }
        if (!in_array($settings['upload_medium_fixed_dimension'], ['width', 'height'])) {
            $settings['upload_medium_fixed_dimension'] = 'width';
        }
        $settings['listing_device_to_columns'] = [];
        foreach (array_keys($device_to_columns) as $k) {
            $settings['listing_device_to_columns'][$k] = $settings['listing_columns_' . $k];
        }
        $settings['listing_device_to_columns']['largescreen'] = $settings['listing_columns_desktop'];
        $settings = array_merge($settings, [
            'username_min_length' => 3,
            'username_max_length' => 16,
            'username_pattern' => '^[\w]{3,16}$',
            'user_password_min_length' => 6,
            'user_password_max_length' => 128,
            'user_password_pattern' => '^.{6,128}$',
            'maintenance_image' => 'default/maintenance_cover.jpg',
            'ip_whois_url' => 'https://ipinfo.io/%IP',
            'available_button_colors' => ['blue', 'green', 'orange', 'red', 'grey', 'black', 'white', 'default'],
            'routing_regex' => '([\w_-]+)',
            'routing_regex_path' => '([\w\/_-]+)',
            'single_user_mode_on_disables' => ['enable_signups', 'guest_uploads', 'user_routing'],
            'listing_safe_count' => 100,
            'image_title_max_length' => 100,
            'album_name_max_length' => 100,
            'upload_available_image_formats' => 'jpg,jpeg,png,bmp,gif,webp',
        ]);
        if (!array_key_exists('active_storage', $settings)) {
            $settings['active_storage'] = null;
        }
        foreach ([
            'CHEVERETO_ENABLE_CONSENT_SCREEN' => ['0',
                [
                    'enable_consent_screen' => false
                ]
            ],
            'CHEVERETO_ENABLE_COOKIE_COMPLIANCE' => ['0',
                [
                    'enable_cookie_law' => false
                ]
            ],
            'CHEVERETO_ENABLE_UPLOAD_PLUGIN' => ['0',
                [
                    'enable_plugin_route' => false
                ]
            ],
            'CHEVERETO_ENABLE_FOLLOWERS' => ['0',
                [
                    'enable_followers' => false
                ]
            ],
            'CHEVERETO_ENABLE_LIKES' => ['0',
                [
                    'enable_likes' => false
                ]
            ],
            'CHEVERETO_ENABLE_MODERATION' => ['0',
                [
                    'moderate_uploads' => ''
                ]
            ],
            'CHEVERETO_ENABLE_POWERED_BY_FOOTER_SITE_WIDE' => ['1',
                [
                    'enable_powered_by' => true
                ]
            ],
            'CHEVERETO_ENABLE_UPLOAD_FLOOD_PROTECTION' => ['0',
                [
                    'flood_uploads_protection' => false
                ]
            ],
            'CHEVERETO_ENABLE_FAVICON' => ['0',
                [
                    'favicon_image' => 'default/favicon.png',
                ]
            ],
            'CHEVERETO_ENABLE_LOGO' => ['0',
                [
                    'logo_type' => 'vector',
                    'logo_image' => 'default/logo.png',
                    'logo_vector' => 'default/logo.svg',
                    'theme_logo_height' => '',
                ]
            ],
            'CHEVERETO_ENABLE_USERS' => ['0',
                [
                    'website_mode' => 'personal',
                    'website_mode_personal_uid' => 1,
                    'website_mode_personal_routing' => '/',
                    'image_lock_nsfw_editing' => false,
                    'stop_words' => '',
                    'show_banners_in_nsfw' => false,
                ]
            ],
            'CHEVERETO_ENABLE_ROUTING' => ['0',
                [
                    'route_user' => 'user',
                    'root_route' => 'user',
                    'route_image' => 'image',
                    'route_album' => 'album',
                ]
            ],
            'CHEVERETO_ENABLE_CDN' => ['0',
                [
                    'cdn' => false,
                    'cdn_url' => ''
                ]
            ],
            'CHEVERETO_ENABLE_SERVICE_AKISMET' => ['0',
                [
                    'akismet' => false,
                    'akismet_api_key' => ''
                ]
            ],
            'CHEVERETO_ENABLE_SERVICE_PROJECTARACHNID' => ['0',
                [
                    'arachnid' => false,
                    'arachnid_key' => ''
                ]
            ],
            'CHEVERETO_ENABLE_SERVICE_STOPFORUMSPAM' => ['0',
                [
                    'stopforumspam' => false,
                ]
            ],
            'CHEVERETO_ENABLE_SERVICE_MODERATECONTENT' => ['0',
                [
                    'moderatecontent' => false,
                    'moderatecontent_key' => ''
                ]
            ],
            'CHEVERETO_ENABLE_CAPTCHA' => ['0',
                [
                    'captcha' => false,
                    'captcha_secret' => '',
                    'captcha_sitekey' => '',
                    'captcha_threshold' => '',
                    'force_captcha_contact_page' => false,
                ]
            ],
            'CHEVERETO_ENABLE_LANGUAGE_CHOOSER' => ['0',
                [
                    'auto_language' => false,
                    'language_chooser_enable' => false,
                ]
            ],
            'CHEVERETO_ENABLE_UPLOAD_WATERMARK' => ['0',
                [
                    'watermark_enable_admin' => false,
                    'watermark_enable_file_gif' => false,
                    'watermark_enable_guest' => false,
                    'watermark_enable_user' => false,
                    'watermark_enable' => false,
                    'watermark_image' => 'default/watermark.png',
                    'watermark_margin' => '10',
                    'watermark_opacity' => '50',
                    'watermark_percentage' => '4',
                    'watermark_position' => 'center center',
                    'watermark_target_min_height' => '100',
                    'watermark_target_min_width' => '100',
                ]
            ],
        ] as $envKey => $settingValues) {
            if (env()[$envKey] == $settingValues[0]) {
                foreach ($settingValues[1] as $k => $v) {
                    $settings[$k] = $v;
                }
            }
        }
        foreach ($settings as $k => &$v) {
            nullify_string($v);
        }
        foreach ($defaults as $k => &$v) {
            nullify_string($v);
        }
        if (isset($settings['theme_logo_height'])) {
            $settings['theme_logo_height'] = (int) $settings['theme_logo_height'];
        }
        if ($settings['website_mode'] == 'personal') {
            if (array_key_exists('website_mode_personal_routing', $settings)) {
                if (is_null($settings['website_mode_personal_routing']) || $settings['website_mode_personal_routing'] == '/') {
                    $settings['website_mode_personal_routing'] = '/';
                } else {
                    $settings['website_mode_personal_routing'] = get_regex_match($settings['routing_regex'], $settings['website_mode_personal_routing'], '#', 1);
                }
            }
            if (!is_integer($settings['website_mode_personal_uid'])) {
                $settings['website_mode_personal_uid'] = 1;
            }

            foreach ($settings['single_user_mode_on_disables'] as $k) {
                $settings[$k] = false;
            }
            $settings['enable_likes'] = false;
            $settings['enable_followers'] = false;
        }
        if (is_null($settings['homepage_cta_fn'])) {
            $settings['homepage_cta_fn'] = 'cta-upload';
        }
        if ($settings['homepage_cta_fn'] == 'cta-link' && !is_url($settings['homepage_cta_fn_extra'])) {
            $settings['homepage_cta_fn_extra'] = get_regex_match($settings['routing_regex_path'], $settings['homepage_cta_fn_extra'], '#', 1);
        }
        if (!is_null($settings['languages_disable'])) {
            $languages_disable = (array) explode(',', $settings['languages_disable']);
            $languages_disable = array_filter(array_unique($languages_disable));
        } else {
            $languages_disable = [];
        }
        $settings['languages_disable'] = $languages_disable;
        if (hasEncryption()) {
            $settings = decryptValues(self::ENCRYPTED_NAMES, $settings);
        }
        self::$settings = $settings;
        self::$defaults = $defaults;
        self::$typeset = $typeset;
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                message('No %type% initialized')
                    ->withCode('%type%', static::class),
                600
            );
        }

        return self::$instance;
    }

    public static function getStatic(string $var): mixed
    {
        $instance = self::getInstance();

        return $instance::$$var;
    }

    public static function get(?string $key = null): mixed
    {
        $settings = self::getStatic('settings');
        if ($key === null) {
            return $settings;
        }
        $value = $settings[$key] ?? null;
        $typeset = self::getStatic('typeset');

        return match ($typeset[$key] ?? null) {
            'bool' => (bool) $value,
            default => $value,
        };
    }

    public static function getTypeset(string $key): string
    {
        $typeset = self::getStatic('typeset');

        return $typeset[$key] ?? '!';
    }

    public static function hasKey(string $key): bool
    {
        $settings = self::getStatic('settings');

        return array_key_exists($key, $settings);
    }

    public static function getType(int|string $val): string
    {
        return ($val === 0 || $val === 1) ? 'bool' : 'string';
    }

    public static function getDefaults(?string $key = null): mixed
    {
        $defaults = self::getStatic('defaults');
        if (!is_null($key)) {
            return $defaults[$key];
        } else {
            return $defaults;
        }
    }

    public static function getDefault(string $key): mixed
    {
        return self::getDefaults($key);
    }

    public static function setValues(array $values): void
    {
        self::$settings = $values;
    }

    public static function setValue(string $key, mixed $value): void
    {
        self::$settings[$key] = $value ?? null;
    }

    public static function insert(array $keyValues): bool
    {
        $query = '';
        $binds = [];
        $table = DB::getTable('settings');
        $query_tpl =
            <<<SQL
            INSERT INTO `$table` (setting_name, setting_value, setting_default, setting_typeset) VALUES (%name, %value, %value, %typeset);
            SQL;
        $plainText = $keyValues;
        if (hasEncryption()) {
            $keyValues = encryptValues(self::ENCRYPTED_NAMES, $keyValues);
        }
        $i = 0;
        foreach ($keyValues as $k => $v) {
            $value = $plainText[$k];
            $query .= strtr(
                $query_tpl,
                [
                    '%name' => ':n_' . $i,
                    '%value' => ':v_' . $i,
                    '%typeset' => ':t_' . $i,
                ]
            );
            $binds[':n_' . $i] = $k;
            $binds[':v_' . $i] = $v;
            $binds[':t_' . $i] = ($value === 0 || $value === 1) ? 'bool' : 'string';
            ++$i;
        }
        unset($i);
        $db = DB::getInstance();
        $db->query($query);
        foreach ($binds as $bindK => $bindV) {
            $db->bind($bindK, $bindV);
        }
        $db->exec();

        return true;
    }

    public static function update(array $keyValues): bool
    {
        $query = '';
        $binds = [];
        $query_tpl = 'UPDATE `'
            . DB::getTable('settings')
            . '` SET `setting_value` = %v WHERE `setting_name` = %k;' . "\n";
        $plainText = $keyValues;
        if (hasEncryption()) {
            $keyValues = encryptValues(self::ENCRYPTED_NAMES, $keyValues);
        }
        $i = 0;
        foreach ($keyValues as $k => $v) {
            self::setValue($k, $plainText[$k]);
            $query .= strtr(
                $query_tpl,
                ['%v' => ':v_' . $i, '%k' => ':n_' . $i]
            );
            $binds[':v_' . $i] = $v;
            $binds[':n_' . $i] = $k;
            ++$i;
        }
        unset($i);
        $db = DB::getInstance();
        $db->query($query);
        foreach ($binds as $bindK => $bindV) {
            $db->bind($bindK, $bindV);
        }

        return $db->exec();
    }

    /**
     * @deprecate
     */
    public static function getChevereto(): array
    {
        $api = 'https://chevereto.com/api/';

        return [
            'id' => '',
            'edition' => APP_NAME,
            'version' => APP_VERSION,
            'source' => [
                'label' => 'chevereto.com',
                'url' => 'https://chevereto.com/panel/downloads',
            ],
            'api' => [
                'download' => $api . 'download',
                'get' => [
                    'info' => $api . 'get/info/4',
                ],
            ],
        ];
    }
}
