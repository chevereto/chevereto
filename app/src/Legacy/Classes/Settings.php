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

use Exception;
use LogicException;
use function Chevere\Message\message;
use function Chevereto\Encryption\decryptValues;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\G\get_regex_match;
use function Chevereto\Legacy\G\is_integer;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\strip_tags_content;
use function Chevereto\Vars\env;

class Settings
{
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
        'asset_storage_server',
        'asset_storage_service',
        'asset_storage_account_id',
        'asset_storage_account_name',
        'asset_storage_key',
        'asset_storage_secret',
        'asset_storage_bucket',
        'arachnid_api_username',
        'arachnid_api_password',
    ];

    public const ALLOW_HTML = [
        'homepage_title_html',
        'homepage_paragraph_html',
        'homepage_cta_html',
    ];

    public const SEMANTICS = [
        [
            'semantics_album' => 'Album',
            'semantics_albums' => 'Albums',
        ],
        [
            'semantics_image' => 'Image',
            'semantics_images' => 'Images',
        ],
        [
            'semantics_user' => 'User',
            'semantics_users' => 'Users',
        ],
        [
            'semantics_category' => 'Category',
            'semantics_categories' => 'Categories',
        ],
        [
            'semantics_explore' => 'Explore',
        ],
        [
            'semantics_discovery' => 'Discovery',
        ],
    ];

    public const SEMANTICS_REGEX = '^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$';

    public const ENV_TO_SETTINGS = [
        'CHEVERETO_ENABLE_CONSENT_SCREEN' => ['0',
            [
                'enable_consent_screen' => false,
            ],
        ],
        'CHEVERETO_ENABLE_COOKIE_COMPLIANCE' => ['0',
            [
                'enable_cookie_law' => false,
            ],
        ],
        'CHEVERETO_ENABLE_UPLOAD_PLUGIN' => ['0',
            [
                'enable_plugin_route' => false,
            ],
        ],
        'CHEVERETO_ENABLE_FOLLOWERS' => ['0',
            [
                'enable_followers' => false,
            ],
        ],
        'CHEVERETO_ENABLE_LIKES' => ['0',
            [
                'enable_likes' => false,
            ],
        ],
        'CHEVERETO_ENABLE_MODERATION' => ['0',
            [
                'moderate_uploads' => '',
            ],
        ],
        'CHEVERETO_ENABLE_FORCE_POWERED_BY_FOOTER' => ['1',
            [
                'enable_powered_by' => true,
            ],
        ],
        'CHEVERETO_ENABLE_UPLOAD_FLOOD_PROTECTION' => ['0',
            [
                'flood_uploads_protection' => false,
            ],
        ],
        'CHEVERETO_ENABLE_FAVICON' => ['0',
            [
                'favicon_image' => 'default/favicon.png',
            ],
        ],
        'CHEVERETO_ENABLE_LOGO_CUSTOM' => ['0',
            [
                'logo_type' => 'vector',
                'logo_image' => 'default/logo.png',
                'logo_vector' => 'default/logo.svg',
                'theme_logo_height' => '',
            ],
        ],
        'CHEVERETO_ENABLE_USERS' => ['0',
            [
                'website_mode' => 'personal',
                'website_mode_personal_uid' => 1,
                'website_mode_personal_routing' => '/',
                'image_lock_nsfw_editing' => false,
                'stop_words' => '',
                'show_banners_in_nsfw' => false,
            ],
        ],
        'CHEVERETO_ENABLE_ROUTING' => ['0',
            [
                'route_user' => 'user',
                'root_route' => 'user',
                'route_image' => 'image',
                'route_album' => 'album',
            ],
        ],
        'CHEVERETO_ENABLE_CDN' => ['0',
            [
                'cdn' => false,
                'cdn_url' => '',
            ],
        ],
        'CHEVERETO_ENABLE_SERVICE_AKISMET' => ['0',
            [
                'akismet' => false,
                'akismet_api_key' => '',
            ],
        ],
        'CHEVERETO_ENABLE_SERVICE_PROJECTARACHNID' => ['0',
            [
                'arachnid' => false,
                'arachnid_api_username' => '',
                'arachnid_api_password' => '',
            ],
        ],
        'CHEVERETO_ENABLE_SERVICE_STOPFORUMSPAM' => ['0',
            [
                'stopforumspam' => false,
            ],
        ],
        'CHEVERETO_ENABLE_SERVICE_MODERATECONTENT' => ['0',
            [
                'moderatecontent' => false,
                'moderatecontent_key' => '',
            ],
        ],
        'CHEVERETO_ENABLE_CAPTCHA' => ['0',
            [
                'captcha' => false,
                'captcha_secret' => '',
                'captcha_sitekey' => '',
                'captcha_threshold' => '',
                'force_captcha_contact_page' => false,
            ],
        ],
        'CHEVERETO_ENABLE_LANGUAGE_CHOOSER' => ['0',
            [
                'auto_language' => false,
                'language_chooser_enable' => false,
            ],
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
            ],
        ],
        'CHEVERETO_ENABLE_SEO_IMAGE_URL' => ['0',
            [
                'seo_image_urls' => false,
            ],
        ],
        'CHEVERETO_ENABLE_SEO_ALBUM_URL' => ['0',
            [
                'seo_album_urls' => false,
            ],
        ],
        'CHEVERETO_ENABLE_UPLOAD_URL' => ['0',
            [
                'enable_uploads_url' => false,
            ],
        ],
        'CHEVERETO_ENABLE_DEBUG' => ['0',
            [
                'debug_errors' => false,
                'dump_update_query' => false,
            ],
        ],
        'CHEVERETO_ENABLE_UPDATE_CHECK' => ['0',
            [
                'enable_automatic_updates_check' => false,
                'update_check_display_notification' => false,
            ],
        ],
        'CHEVERETO_ENABLE_PUP_CUSTOM_URL' => ['0',
            [
                'sdk_pup_url' => '',
            ],
        ],
    ];

    public const STOCK = [
        'akismet' => false,
        'arachnid' => false,
        'asset_storage_account_id' => '',
        'asset_storage_account_name' => '',
        'asset_storage_api_id' => '',
        'asset_storage_bucket' => '',
        'asset_storage_key' => '',
        'asset_storage_region' => '',
        'asset_storage_secret' => '',
        'asset_storage_server' => '',
        'asset_storage_service' => '',
        'asset_storage_url' => '',
        'asset_storage_use_path_style_endpoint' => false,
        'auto_language' => true,
        'cache_ttl' => '0',
        'captcha_api' => 'hcaptcha',
        'comments_api' => 'js',
        'debug_errors' => false,
        'default_language' => 'en',
        'dump_update_query' => false,
        'enable_automatic_updates_check' => true,
        'enable_consent_screen' => false,
        'enable_duplicate_uploads' => false,
        'enable_expirable_uploads' => null,
        'enable_followers' => true,
        'enable_likes' => true,
        'enable_plugin_route' => true,
        'enable_powered_by' => true,
        'enable_signups' => true,
        'enable_uploads_url' => false,
        'enable_user_content_delete' => false,
        'explore_albums_min_image_count' => 5,
        'force_captcha_contact_page' => true,
        'guest_albums' => false,
        'homepage_cta_color' => 'accent',
        'homepage_cta_fn' => 'cta-upload',
        'homepage_cta_outline' => false,
        'homepage_style' => 'landing',
        'hostname' => null,
        'image_first_tab' => 'about',
        'image_load_max_filesize_mb' => '5',
        'image_lock_nsfw_editing' => false,
        'language_chooser_enable' => true,
        'languages_disable' => null,
        'listing_pagination_mode' => 'classic',
        'listing_viewer' => true,
        'logo_type' => 'vector',
        'moderate_uploads' => '',
        'moderatecontent_block_rating' => 'a',
        'moderatecontent_flag_nsfw' => 'a',
        'moderatecontent_key' => '',
        'moderatecontent' => false,
        'notify_user_signups' => false,
        'require_user_email_confirmation' => true,
        'require_user_email_social_signup' => true,
        'root_route' => 'user',
        'route_album' => 'album',
        'route_audio' => 'audio',
        'route_image' => 'image',
        'route_user' => 'user',
        'route_video' => 'video',
        'sdk_pup_url' => null,
        'seo_album_urls' => true,
        'seo_image_urls' => true,
        'stopforumspam' => false,
        'theme_download_button' => true,
        'theme_image_right_click' => false,
        'theme_palette' => '10',
        'theme_show_embed_content_for' => 'all',
        'theme_show_embed_uploader' => true,
        'theme_show_exif_data' => true,
        'theme_show_social_share' => true,
        'upload_enabled_image_formats' => 'avif,jpg,png,bmp,gif,webp,mov,mp4,webm',
        'upload_gui' => 'js',
        'upload_max_filesize_mb_guest' => '10',
        'upload_max_image_height' => '0',
        'upload_max_image_width' => '0',
        'upload_medium_fixed_dimension' => 'width',
        'upload_medium_size' => 500,
        'upload_threads' => '2',
        'user_image_avatar_max_filesize_mb' => '1',
        'user_image_background_max_filesize_mb' => '2',
        'user_minimum_age' => null,
        'user_profile_view' => 'files',
        'user_routing' => true,
        'watermark_enable_admin' => true,
        'watermark_enable_file_gif' => false,
        'watermark_enable_guest' => true,
        'watermark_enable_user' => true,
        'watermark_percentage' => 4,
        'watermark_target_min_height' => 100,
        'watermark_target_min_width' => 100,
        'website_content_privacy_mode' => 'default',
        'website_explore_page_guest' => true,
        'website_explore_page' => true,
        'website_mode' => 'community',
        'website_privacy_mode' => 'public',
        'website_random_guest' => true,
        'website_random' => true,
        'website_search_guest' => true,
        'website_search' => true,
        'arachnid_api_username' => '',
        'arachnid_api_password' => '',
    ];

    public const USERNAME_MIN_LENGTH = 3;

    public const USERNAME_MAX_LENGTH = 16;

    public const USERNAME_PATTERN = '^[\w]{3,16}$';

    public const USER_PASSWORD_MIN_LENGTH = 6;

    public const USER_PASSWORD_MAX_LENGTH = 128;

    public const USER_PASSWORD_PATTERN = '^.{6,128}$';

    public const MAINTENANCE_IMAGE = 'default/maintenance_cover.jpg';

    public const IP_WHOIS_URL = 'https://ipinfo.io/%IP';

    public const AVAILABLE_BUTTON_COLORS = ['blue', 'green', 'orange', 'red', 'grey', 'black', 'white', 'default', 'accent'];

    public const ROUTING_REGEX = '([\w_-]+)';

    public const ROUTING_REGEX_PATH = '([\w\/_-]+)';

    public const SINGLE_USER_MODE_ON_DISABLES = ['enable_signups', 'guest_uploads', 'user_routing'];

    public const LISTING_SAFE_COUNT = 100;

    public const IMAGE_TITLE_MAX_LENGTH = 100;

    public const ALBUM_NAME_MAX_LENGTH = 100;

    public const UPLOAD_AVAILABLE_IMAGE_FORMATS = 'avif,jpg,jpeg,png,bmp,gif,webp,mov,mp4,webm';

    /**
     * @var array<string>
     */
    protected static array $envRestricted = [];

    protected static ?self $instance;

    protected static array $settings = [];

    protected static array $defaults = [];

    protected static array $typeset = [];

    protected static array $decrypted = [];

    public function __construct()
    {
        $settings = [];
        $defaults = [];
        $typeset = [];
        $db_settings_fix = [];

        try {
            $db_settings = DB::get(
                table: 'settings',
                where: 'all',
                sort: [
                    'field' => 'name',
                    'order' => 'asc',
                ]
            );

            foreach ($db_settings as $k => $v) {
                $v = DB::formatRow($v);
                $value = $v['value'];
                $default = $v['default'];
                if ($v['typeset'] === 'bool') {
                    $value = $value === '1';
                    $default = $default === '1';
                }
                if ($v['typeset'] === 'string') {
                    $value = (string) $value;
                    $default = (string) $default;
                    if (! in_array($v['name'], self::ALLOW_HTML, true)) {
                        $valueStrip = strip_tags_content($value);
                        if ($value !== $valueStrip) {
                            $db_settings_fix[$v['name']] = $valueStrip;
                            $value = $valueStrip;
                        }
                    }
                }
                $typeset[$v['name']] = $v['typeset'];
                $settings[$v['name']] = $value;
                $defaults[$v['name']] = $default;
            }
        } catch (Exception) {
            $settings = [];
            $defaults = [];
        }
        $device_to_columns = [
            'phone' => 1,
            'phablet' => 3,
            'tablet' => 4,
            'laptop' => 5,
            'desktop' => 6,
        ];
        $stock = self::STOCK;
        foreach ($device_to_columns as $k => $v) {
            $stock['listing_columns_' . $k] = $v;
        }
        foreach ($stock as $k => $v) {
            if (! array_key_exists($k, $settings)) {
                $settings[$k] = $v;
                $defaults[$k] = $v;
            }
        }
        if (isset($settings['email_mode']) && $settings['email_mode'] === 'phpmail') {
            $settings['email_mode'] = 'mail';
        }
        if (! in_array($settings['upload_medium_fixed_dimension'], ['width', 'height'], true)) {
            $settings['upload_medium_fixed_dimension'] = 'width';
        }
        $settings['listing_device_to_columns'] = [];
        foreach (array_keys($device_to_columns) as $k) {
            $settings['listing_device_to_columns'][$k] = $settings['listing_columns_' . $k];
        }
        $settings['listing_device_to_columns']['largescreen'] = $settings['listing_columns_desktop'];
        if (! array_key_exists('active_storage', $settings)) {
            $settings['active_storage'] = null;
        }
        foreach (static::ENV_TO_SETTINGS as $envKey => $settingValues) {
            if (! array_key_exists($envKey, env())) {
                continue;
            }
            if (env()[$envKey] === $settingValues[0]) {
                foreach ($settingValues[1] as $k => $v) {
                    $settings[$k] = $v;
                    if (! in_array($k, static::$envRestricted, true)) {
                        static::$envRestricted[] = $k;
                    }
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
        if ($settings['website_mode'] === 'personal') {
            if (array_key_exists('website_mode_personal_routing', $settings)) {
                if ($settings['website_mode_personal_routing'] == null || $settings['website_mode_personal_routing'] === '/') {
                    $settings['website_mode_personal_routing'] = '/';
                } else {
                    $settings['website_mode_personal_routing'] = get_regex_match(
                        self::ROUTING_REGEX,
                        $settings['website_mode_personal_routing'],
                        '#',
                        1
                    );
                }
            }
            if (! is_integer($settings['website_mode_personal_uid'])) {
                $settings['website_mode_personal_uid'] = 1;
            }
            foreach (self::SINGLE_USER_MODE_ON_DISABLES as $k) {
                $settings[$k] = false;
            }
            $settings['enable_likes'] = false;
            $settings['enable_followers'] = false;
        }
        if ($settings['homepage_cta_fn'] == null) {
            $settings['homepage_cta_fn'] = 'cta-upload';
        }
        if ($settings['homepage_cta_fn'] === 'cta-link' && ! is_url($settings['homepage_cta_fn_extra'])) {
            $settings['homepage_cta_fn_extra'] = get_regex_match(
                self::ROUTING_REGEX_PATH,
                $settings['homepage_cta_fn_extra'],
                '#',
                1
            );
        }
        if ($settings['languages_disable'] != null) {
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
        self::update($db_settings_fix);
    }

    public static function getInstance(): self
    {
        if (! isset(self::$instance)) {
            throw new LogicException(
                message('No `%type%` initialized', type: static::class),
                600
            );
        }

        return self::$instance;
    }

    public static function getStatic(string $var): mixed
    {
        $instance = self::getInstance();

        return $instance::${$var};
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
        if ($key !== null) {
            return $defaults[$key];
        }

        return $defaults;
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
            INSERT INTO `{$table}` (setting_name, setting_value, setting_default, setting_typeset)
            VALUES (%name, %value, %value, %typeset);
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
        if ($keyValues === []) {
            return false;
        }
        $query = '';
        $binds = [];
        $query_tpl = 'UPDATE `'
            . DB::getTable('settings')
            . '` SET `setting_value` = %v WHERE `setting_name` = %k;'
            . "\n";
        $plainText = $keyValues;
        if (hasEncryption()) {
            $keyValues = encryptValues(self::ENCRYPTED_NAMES, $keyValues);
        }
        $i = 0;
        $restricted = [];
        foreach ($keyValues as $k => $v) {
            if (static::isEnvRestricted($k)) {
                $restricted[] = $k;

                continue;
            }
            $typeset = self::getTypeset($k);
            if ($typeset === 'bool') {
                $v = (int) (intval($v) === 1 || strtolower($v) === 'true');
                $plainText[$k] = $v;
            }
            if (is_string($v)
                && ! in_array($k, self::ALLOW_HTML, true)
            ) {
                $v = strip_tags_content($v);
            }
            self::setValue($k, $plainText[$k]);
            $query .= strtr(
                $query_tpl,
                [
                    '%v' => ':v_' . $i,
                    '%k' => ':n_' . $i,
                ]
            );
            $binds[':v_' . $i] = $v;
            $binds[':n_' . $i] = $k;
            ++$i;
        }
        unset($i);
        if ($query === '') {
            return $restricted === []
                ? false
                : throw new LogicException(
                    message('Trying to modify restricted setting(s): %restricted%', restricted: implode(', ', $restricted)),
                    600
                );
        }
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

    public static function isEnvRestricted(string $key): bool
    {
        return in_array($key, self::$envRestricted, true);
    }
}
