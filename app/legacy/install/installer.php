<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\Filesystem\filePhpForPath;
use function Chevere\Message\message;
use function Chevere\String\randomString;
use Chevere\Throwable\Exceptions\LogicException;
use function Chevereto\Encryption\encryptValues;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Encryption\randomKey;
use function Chevereto\Legacy\chevereto_die;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\bytes_to_mb;
use function Chevereto\Legacy\G\debug;
use function Chevereto\Legacy\G\get_ini_bytes;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\hasEnvDbInfo;
use function Chevereto\Legacy\G\logger;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\set_status_header;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\get_chevereto_version;
use function Chevereto\Legacy\getPreCodeHtml;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\env;
use function Chevereto\Vars\post;

if (PHP_SAPI !== 'cli') {
    /** @var Handler $handler */
    $context = $handler->request_array()[0] ?? false;
    if (!$context) {
        throw new LogicException(message('Missing context'));
    }
    if (!in_array($context, ['install', 'update'])) {
        throw new LogicException(message('Invalid context'));
    }
}
if (!is_null(getSetting('chevereto_version_installed')) && (PHP_SAPI !== 'cli' && !Login::isAdmin())) {
    set_status_header(403);

    throw new LogicException(message('Request denied. You must be an admin to be here.'), 403);
}
if (function_exists('opcache_reset')) {
    try {
        opcache_reset();
        // @phpstan-ignore-next-line
    } catch (Throwable) {
        // Ignore, Zend OPcache API is restricted by "restrict_api" configuration directive
    }
}
$doctitles = [
    'connect' => 'Database connection',
    'ready' => 'Ready to install',
    'finished' => 'Installation complete',
    'env' => 'Update app/env.php',
    'already' => 'Already installed',
    'update' => 'Update needed',
    'updated' => 'Update complete',
    'update_failed' => 'Update failed',
];
$doing = 'connect';
$db_array = [
    'db_host' => true,
    'db_name' => true,
    'db_user' => true,
    'db_pass' => false,
    'db_tablePrefix' => false,
    'db_port' => false,
];
$error = false;
$db_conn_error = "Can't connect to the target database. The server replied with this:<br>%s<br><br>Please fix your MySQL info.";
$settings_updates = [
    '3.0.0' => [
        'analytics_code' => '',
        'auto_language' => 1,
        'chevereto_version_installed' => APP_VERSION,
        'comment_code' => '',
        'crypt_salt' => randomString(8),
        'default_language' => 'en',
        'default_timezone' => 'America/Santiago',
        'email_from_email' => 'from@chevereto.example',
        'email_from_name' => 'Chevereto',
        'email_incoming_email' => 'incoming@chevereto.example',
        'email_mode' => 'mail',
        'email_smtp_server' => '',
        'email_smtp_server_password' => '',
        'email_smtp_server_port' => '',
        'email_smtp_server_security' => '',
        'email_smtp_server_username' => '',
        'enable_uploads' => 1,
        // 'facebook' => 0, // Deprecated in 4.0.0-beta.11
        // 'facebook_app_id' => '',
        // 'facebook_app_secret' => '',
        'flood_uploads_day' => '1000',
        'flood_uploads_hour' => '500',
        'flood_uploads_minute' => '50',
        'flood_uploads_month' => '10000',
        'flood_uploads_notify' => 0,
        'flood_uploads_protection' => 1,
        'flood_uploads_week' => '5000',
        // 'google' => 0, // Deprecated in 4.0.0-beta.11
        // 'google_client_id' => '',
        // 'google_client_secret' => '',
        'guest_uploads' => 1,
        'listing_items_per_page' => '24',
        'maintenance' => 0,
        'captcha' => 0, //recaptcha
        'captcha_secret' => '', //recaptcha_private_key
        'captcha_sitekey' => '', //recaptcha_public_key
        'captcha_threshold' => '5', //recaptcha_threshold
        'theme' => 'Peafowl',
        // 'twitter' => 0, // Deprecated in 4.0.0-beta.11
        // 'twitter_api_key' => '',
        // 'twitter_api_secret' => '',
        'upload_filenaming' => 'original',
        'upload_image_path' => 'images',
        'upload_max_filesize_mb' => (string) min(25, bytes_to_mb(get_ini_bytes(ini_get('upload_max_filesize')))),
        'upload_medium_size' => '500', // upload_medium_width
        'upload_storage_mode' => 'datefolder',
        'upload_thumb_height' => '320',
        'upload_thumb_width' => '320',
        'website_description' => 'A free image hosting service powered by Chevereto',
        'website_doctitle' => 'Chevereto image hosting',
        'website_name' => 'Chevereto',
    ],
    '3.0.1' => null,
    '3.0.2' => null,
    '3.0.3' => null,
    '3.0.4' => null,
    '3.0.5' => null,

    '3.1.0' => [
        'website_explore_page' => 1,
        //'theme_peafowl_home_uid' => '1'
    ],
    '3.1.1' => null,
    '3.1.2' => null,

    '3.2.0' => [
        'twitter_account' => 'chevereto',
        //'theme_peafowl_download_button' => 1,
        'enable_signups' => 1,
    ],
    '3.2.1' => null,
    '3.2.2' => [
        'favicon_image' => 'default/favicon.png',
        'logo_image' => 'default/logo.png',
        'logo_vector' => 'default/logo.svg',
        'theme_custom_css_code' => '',
        'theme_custom_js_code' => '',
    ],
    '3.2.3' => [
        'website_keywords' => 'image sharing, image hosting, chevereto',
        // 'logo_vector_enable' => 1,
        'watermark_enable' => 0,
        'watermark_image' => 'default/watermark.png',
        'watermark_position' => 'center center',
        'watermark_margin' => '10',
        'watermark_opacity' => '50',
        //'banner_home_before_cover' => NULL,
        'banner_home_after_cover' => '',
        'banner_home_after_listing' => '',
        'banner_image_image-viewer_foot' => '',
        'banner_image_image-viewer_top' => '',
        'banner_image_after_image-viewer' => '',
        'banner_image_after_header' => '',
        'banner_image_before_header' => '',
        'banner_image_footer' => '',
        'banner_content_tab-about_column' => '',
        'banner_content_before_comments' => '',
        'banner_explore_after_top' => '',
        'banner_user_after_top' => '',
        'banner_user_before_listing' => '',
        'banner_album_before_header' => '',
        'banner_album_after_header' => '',
    ],
    '3.2.4' => null,
    '3.2.5' => [
        'api_v1_key' => randomString(64),
    ],
    '3.2.6' => null,

    '3.3.0' => [
        'listing_pagination_mode' => 'classic',
        'banner_listing_before_pagination' => '',
        'banner_listing_after_pagination' => '',
        'show_nsfw_in_listings' => 0,
        'show_banners_in_nsfw' => 0,
        //'theme_peafowl_nsfw_upload_checkbox' => 1,
        //'privacy_mode' => 'public',
        //'website_mode' => 'public',
        'website_privacy_mode' => 'public',
        'website_content_privacy_mode' => 'default',
    ],
    '3.3.1' => null,
    '3.3.2' => [
        'show_nsfw_in_random_mode' => 0,
    ],

    '3.4.0' => [
        //'theme_peafowl_tone' => 'light',
        //'theme_peafowl_image_listing_size' => 'fixed'
    ],
    '3.4.1' => null,
    '3.4.2' => null,
    '3.4.3' => [
        'cdn' => 0,
        'cdn_url' => '',
    ],
    '3.4.4' => [
        'website_search' => 1,
        'website_random' => 1,
        'theme_logo_height' => '',
        'theme_show_social_share' => 1,
        // 'theme_show_embed_content' => 1, // deprecated @3.14.2
        'theme_show_embed_uploader' => 1,
    ],
    '3.4.5' => [
        // 'user_routing' => 1, // deprecated @4.0.0.beta.7
        'require_user_email_confirmation' => 1,
        'require_user_email_social_signup' => 1,
    ],
    '3.4.6' => null,
    '3.5.0' => [
        //'active_storage' => NULL // deprecated
    ],
    '3.5.1' => null,
    '3.5.2' => null,
    '3.5.3' => null,
    '3.5.4' => null,
    '3.5.5' => [
        'last_used_storage' => '',
    ],
    '3.5.6' => null,
    '3.5.7' => [
        // 'vk' => 0, // Deprecated in 4.0.0-beta.11
        // 'vk_client_id' => '',
        // 'vk_client_secret' => '',
    ],
    '3.5.8' => null,
    '3.5.9' => null,
    '3.5.10' => null,
    '3.5.11' => null,
    '3.5.12' => [
        'theme_download_button' => 1,
        'theme_nsfw_upload_checkbox' => 1,
        // 'theme_tone' => 'light', // Renamed in 4.0.0.beta.5
        'theme_image_listing_sizing' => 'fixed',
    ],
    '3.5.13' => null,
    '3.5.14' => null,
    '3.5.15' => [
        'listing_columns_phone' => '3',
        'listing_columns_phablet' => '3',
        'listing_columns_tablet' => '4',
        'listing_columns_laptop' => '5',
        'listing_columns_desktop' => '6',
        //'logged_user_logo_link'		=> 'homepage', // Removed in 3.7.0
        'homepage_style' => 'landing',
        'homepage_cover_image' => 'default/home_cover.jpg',
        'homepage_uids' => '',
        'homepage_endless_mode' => 0,
    ],
    '3.5.16' => null,
    '3.5.17' => null,
    '3.5.18' => null,
    '3.5.19' => [
        'user_image_avatar_max_filesize_mb' => '1',
        'user_image_background_max_filesize_mb' => '2',
    ],
    '3.5.20' => [
        'theme_image_right_click' => 0,
    ],
    '3.5.21' => null,
    '3.6.0' => [
        // 'minify_enable' => 0, // Removed in 3.19
        'theme_show_exif_data' => 1,
        // 'theme_top_bar_color' => 'white', // Removed in 3.15.0
        //'theme_main_color' => null, // Removed in 4.0.0.beta.4
        //'theme_top_bar_button_color' => 'blue', // Removed in 4.0.0.beta.4
        'logo_image_homepage' => 'default/logo_homepage.png',
        'logo_vector_homepage' => 'default/logo_homepage.svg',
        'homepage_cta_color' => 'black',
        'homepage_cta_outline' => 0,
        'watermark_enable_guest' => 1,
        'watermark_enable_user' => 1,
        'watermark_enable_admin' => 1,
    ],
    '3.6.1' => [
        'homepage_title_html' => '',
        'homepage_paragraph_html' => '',
        'homepage_cta_html' => '',
        'homepage_cta_fn' => '',
        'homepage_cta_fn_extra' => '',
        'language_chooser_enable' => 1,
        'languages_disable' => '',
    ],
    '3.6.2' => [
        'website_mode' => 'community',
        'website_mode_personal_routing' => '', //'single_user_mode_routing'
        'website_mode_personal_uid' => '', //'single_user_mode_id'
    ],
    '3.6.3' => null,
    '3.6.4' => [
        'enable_cookie_law' => 0,
        'theme_nsfw_blur' => 0,
    ],
    '3.6.5' => [
        'watermark_target_min_width' => '100',
        'watermark_target_min_height' => '100',
        'watermark_percentage' => '4',
        'watermark_enable_file_gif' => 0,
    ],
    '3.6.6' => [
        'id_padding' => '0', // 0-> Update | 5000-> new install
    ],
    '3.6.7' => null,
    '3.6.8' => [
        'upload_image_exif' => 1,
        'upload_image_exif_user_setting' => 1,
        'enable_expirable_uploads' => 1,
        //'banner_home_before_cover_nsfw'			=> NULL, // Deprecate
        'banner_home_after_cover_nsfw' => '',
        'banner_home_after_listing_nsfw' => '',
        'banner_image_image-viewer_foot_nsfw' => '',
        'banner_image_image-viewer_top_nsfw' => '',
        'banner_image_after_image-viewer_nsfw' => '',
        'banner_image_after_header_nsfw' => '',
        'banner_image_before_header_nsfw' => '',
        'banner_image_footer_nsfw' => '',
        'banner_content_tab-about_column_nsfw' => '',
        'banner_content_before_comments_nsfw' => '',
        'banner_explore_after_top_nsfw' => '',
        'banner_user_after_top_nsfw' => '',
        'banner_user_before_listing_nsfw' => '',
        'banner_album_before_header_nsfw' => '',
        'banner_album_after_header_nsfw' => '',
        'banner_listing_before_pagination_nsfw' => '',
        'banner_listing_after_pagination_nsfw' => '',
    ],
    '3.6.9' => null,
    '3.7.0' => null,
    '3.7.1' => null,
    '3.7.2' => [
        'upload_medium_size' => '750',
        'upload_medium_fixed_dimension' => 'width',
    ],
    '3.7.3' => [
        'enable_followers' => 1,
        'enable_likes' => 1,
        'enable_consent_screen' => 0,
        'user_minimum_age' => '',
        'consent_screen_cover_image' => 'default/consent-screen_cover.jpg',
    ],
    '3.7.4' => null,
    '3.7.5' => [
        'enable_redirect_single_upload' => 0,
        'route_image' => 'image',
        'route_album' => 'album',
    ],
    '3.8.0' => [
        'enable_duplicate_uploads' => 0,
        'update_check_datetimegmt' => '',
        'update_check_notified_release' => APP_VERSION,
        'update_check_display_notification' => 1,
    ],
    '3.8.1' => null,
    '3.8.2' => null,
    '3.8.3' => null, // Chevereto Free hook
    '3.8.4' => [
        'banner_home_before_title' => '',
        'banner_home_after_cta' => '',
        'banner_home_before_title_nsfw' => '',
        'banner_home_after_cta_nsfw' => '',
        // 'upload_enabled_image_formats' => 'jpg,png,bmp,gif',
        'upload_threads' => '2',
        'enable_automatic_updates_check' => 1,
        'comments_api' => 'js',
        'disqus_shortname' => '',
        'disqus_public_key' => '',
        'disqus_secret_key' => '',
    ],
    '3.8.5' => null,
    '3.8.6' => null,
    '3.8.7' => null,
    '3.8.8' => null,
    '3.8.9' => [
        'image_load_max_filesize_mb' => '3',
    ],
    '3.8.10' => null,
    '3.8.11' => null,
    '3.8.12' => [
        'upload_max_image_width' => '0',
        'upload_max_image_height' => '0',
    ],
    '3.8.13' => null,
    '3.9.0' => [
        'auto_delete_guest_uploads' => '',
    ],
    '3.9.1' => null,
    '3.9.2' => null,
    '3.9.3' => null,
    '3.9.4' => null,
    '3.9.5' => null,
    '3.10.0' => null,
    '3.10.1' => null,
    '3.10.2' => [
        'enable_user_content_delete' => 1,
    ],
    '3.10.3' => [
        'enable_plugin_route' => 1,
        'sdk_pup_url' => '',
    ],
    '3.10.4' => null,
    '3.10.5' => null,
    '3.10.6' => [
        'website_explore_page_guest' => 1,
        'explore_albums_min_image_count' => '1',
        'upload_max_filesize_mb_guest' => '0.5',
        'notify_user_signups' => 0,
        'listing_viewer' => 1,
    ],
    '3.10.7' => null,
    '3.10.8' => null,
    '3.10.9' => null,
    '3.10.10' => null,
    '3.10.11' => null,
    '3.10.12' => null,
    '3.10.13' => null,
    '3.10.14' => null,
    '3.10.15' => null,
    '3.10.16' => null,
    '3.10.17' => null,
    '3.10.18' => null,
    '3.11.0' => null,
    '3.11.1' => [
        'seo_image_urls' => 1,
        'seo_album_urls' => 1,
    ],
    '3.12.0' => [
        // 'website_https' => 'auto',
    ],
    '3.12.1' => null,
    '3.12.2' => null,
    '3.12.3' => null,
    '3.12.4' => [
        'upload_gui' => 'js',
        'captcha_api' => 'hcaptcha', //recaptcha_version(2,3),hcaptcha
    ],
    '3.12.5' => null,
    '3.12.6' => null,
    '3.12.7' => null,
    '3.12.8' => [
        'force_captcha_contact_page' => 1, //force_recaptcha_contact_page
    ],
    '3.12.9' => null,
    '3.12.10' => null,
    '3.13.0' => [
        'dump_update_query' => 0,
    ],
    '3.13.1' => null,
    '3.13.2' => null,
    '3.13.3' => null,
    '3.13.4' => [
        'enable_powered_by' => 1,
        'akismet' => 0,
        'akismet_api_key' => '',
        'stopforumspam' => 0,
    ],
    '3.13.5' => null,
    '3.14.0' => [
        'upload_enabled_image_formats' => 'jpg,png,bmp,gif,webp',
    ],
    '3.14.1' => null,
    '3.15.0' => [
        'hostname' => null,
        'theme_show_embed_content_for' => 'all', // none,users,all
    ],
    '3.15.1' => null,
    '3.15.2' => null,
    '3.16.0' => [
        'moderatecontent' => 0,
        'moderatecontent_key' => '',
        'moderatecontent_block_rating' => 'a', // ,a,t
        'moderatecontent_flag_nsfw' => 'a', // ,a,t
        'moderatecontent_auto_approve' => 0,
        'moderate_uploads' => '', // ,all,guest,
        'image_lock_nsfw_editing' => 0,
    ],
    '3.16.1' => null,
    '3.16.2' => null,
    '3.17.0' => null,
    '3.17.1' => null,
    '3.17.2' => null,
    '3.18.0' => null,
    '3.18.1' => null,
    '3.18.2' => null,
    '3.18.3' => null,
    '3.20.0' => null,
    '3.20.1' => null,
    '3.20.2' => null,
    '3.20.3' => null,
    '3.20.4' => null,
    '3.20.5' => null,
    '3.20.6' => null,
    '3.20.7' => null,
    '3.20.8' => [
        'enable_uploads_url' => 0,
    ],
    '3.20.9' => null,
    '3.20.10' => null,
    '3.20.11' => null,
    '3.20.12' => null,
    '3.20.13' => [
        'chevereto_news' => 'a:0:{}',
        'cron_last_ran' => '0000-00-00 00:00:00',
    ],
    '3.20.14' => null,
    '3.20.15' => null,
    '3.20.16' => null,
    '4.0.0.beta.1' => null,
    '4.0.0.beta.2' => null,
    '4.0.0.beta.3' => null,
    '4.0.0.beta.4' => null,
    '4.0.0.beta.5' => [
        'logo_type' => 'vector', // vector,image,text,
        'theme_palette' => '0',
    ],
    '4.0.0.beta.6' => [
        'enable_xr' => 0,
        'xr_host' => env()['CHEVERETO_SERVICING'] === 'docker'
            ? 'host.docker.internal'
            : 'localhost',
        'xr_port' => '27420',
        'xr_key' => '',
    ],
    '4.0.0.beta.7' => [
        'route_user' => 'user',
        'root_route' => 'user',
    ],
    '4.0.0.beta.8' => null,
    '4.0.0.beta.9' => [
        'arachnid' => 0,
        'arachnid_key' => '',
        'image_first_tab' => 'info', //embeds,about,info
    ],
    '4.0.0-beta.10' => null,
    '4.0.0-beta.11' => [
        'website_random_guest' => 1,
        'website_search_guest' => 1,
        'debug_errors' => 0,
        'news_check_datetimegmt' => '',
    ],
    '4.0.0' => null,
    '4.0.1' => null,
    '4.0.2' => null,
    '4.0.3' => null,
    '4.0.4' => [
        'stop_words' =>
            // https://dev.mysql.com/doc/refman/8.0/en/string-literals.html#character-escape-sequences
            <<<'SPAM'
            richard blank
            costa rica[\\\'s]*? call center
            call center sales
            SPAM,
    ],
    '4.0.5' => null,
];
$cheveretoFreeMap = [
    '1.0.0' => '3.8.3',
    '1.0.1' => '3.8.3',
    '1.0.2' => '3.8.3',
    '1.0.3' => '3.8.4',
    '1.0.4' => '3.8.4',
    '1.0.5' => '3.8.8',
    '1.0.6' => '3.8.10',
    '1.0.7' => '3.8.11',
    '1.0.8' => '3.8.13',
    '1.0.9' => '3.9.5',
    '1.0.10' => '3.10.5',
    '1.0.11' => '3.10.5',
    '1.0.12' => '3.10.5',
    '1.0.13' => '3.10.5',
    '1.1.0' => '3.10.18',
    '1.1.1' => '3.10.18',
    '1.1.2' => '3.10.18',
    '1.1.3' => '3.10.18',
    '1.1.4' => '3.10.18',
    '1.2.0' => '3.15.2',
    '1.2.1' => '3.15.2',
    '1.2.2' => '3.15.2',
    '1.2.3' => '3.15.2',
    '1.3.0' => '3.16.2',
    '1.4.0' => '3.16.2',
    '1.5.0' => '3.16.2',
    '1.6.0' => '3.16.2',
    '1.6.1' => '3.16.2',
    '1.6.2' => '3.16.2',
];
$settings_delete = [
    'banner_home_before_cover',
    'banner_home_before_cover_nsfw',
    'cloudflare', // 3.15.0
    'theme_show_embed_content', // 3.15.0
    'theme_top_bar_color', // 3.15.0
    'minify_enable', // 3.19.0,
    'theme_main_color',
    'theme_top_bar_button_color',
    'website_https', // 4.0.5
];
$settings_rename = [
    // 3.5.12
    'theme_peafowl_home_uid' => 'homepage_uids',
    'theme_peafowl_download_button' => 'theme_download_button',
    'theme_peafowl_nsfw_upload_checkbox' => 'theme_nsfw_upload_checkbox',
    'theme_peafowl_image_listing_size' => 'theme_image_listing_sizing',
    // 3.5.15
    'theme_home_uids' => 'homepage_uids',
    'theme_home_endless_mode' => 'homepage_endless_mode',
    // 3.6.2
    'single_user_mode_routing' => 'website_mode_personal_routing',
    'single_user_mode_id' => 'website_mode_personal_uid',
    // 3.7.2
    'upload_medium_width' => 'upload_medium_size',
    // 4.0.3
    'recaptcha_version' => 'captcha_api',
    'recaptcha' => 'captcha',
    'recaptcha_private_key' => 'captcha_secret',
    'recaptcha_public_key' => 'captcha_sitekey',
    'recaptcha_threshold' => 'captcha_threshold',
    'force_recaptcha_contact_page' => 'force_captcha_contact_page',
];
$chv_initial_settings = [];
foreach ($settings_updates as $k => $v) {
    if (is_null($v)) {
        continue;
    }
    $chv_initial_settings += $v;
}

try {
    $is_2X = ((int) DB::get('info', ['key' => 'version'])) > 0;
} catch (Exception $e) {
    $is_2X = false;
}
$isMariaDB = false;
if (hasEnvDbInfo()) {
    if (!DB::hasInstance()) {
        DB::fromEnv();
    }
    $db = DB::getInstance();
    $sqlServerVersion = $db->getAttr(PDO::ATTR_SERVER_VERSION);
    $db->closeCursor();
    $innoDBrequiresVersion = '5.6'; // default:mysql
    if (stripos($sqlServerVersion, 'MariaDB') !== false) {
        $isMariaDB = true;
        $innoDBrequiresVersion = '10.0.5';
        $explodeSqlVersion = explode('-', $sqlServerVersion, 2);
        foreach ($explodeSqlVersion as $pos => $ver) {
            if (str_starts_with($ver, 'MariaDB')) {
                continue;
            }
            $sqlServerVersion = $ver;
        }
    }
    $doing = 'ready';
}
$fulltext_engine = 'InnoDB';
$installed_version = getSetting('chevereto_version_installed');
$maintenance = getSetting('maintenance');
if (isset($installed_version, $cheveretoFreeMap[$installed_version])) {
    $installed_version = $cheveretoFreeMap[$installed_version];
}
if (isset($installed_version)) {
    $doing = 'already';
}
$opts = getopt('C:') ?: [];
$safe_post = Handler::var('safe_post');
if (!empty(post())) {
    $params = post();
    if (isset(post()['debug'])) {
        $params['debug'] = true;
    }
} elseif ($opts !== []) {
    if ($doing == 'already') {
        $opts = getopt('C:d::');
    } else {
        $opts = getopt('C:u:e:x:d::');
        $params = [];
        $missing = [];
        foreach ([
            'username' => 'u',
            'email' => 'e',
            'password' => 'x',
        ] as $k => $o) {
            $params[$k] = $opts[$o] ?? null;
            if (!isset($params[$k])) {
                $missing[] = $k . " -$o";
            }
        }
        if ($missing !== []) {
            logger('Missing ' . implode(', ', $missing) . "\n");
            die(255);
        }
    }
    $params['dump'] = isset($opts['d']);
}
if ($isMariaDB && isset($sqlServerVersion)) {
    $explodeMariaVersion = explode('.', $sqlServerVersion);
    $mariaVersion = ($explodeMariaVersion[0] ?? '10')
        . '.'
        . ($explodeMariaVersion[1] ?? '');
    switch ($mariaVersion) {
        case '10.0':
        case '10.1':
        case '10.2':
        case '10.3':
            $mysql_version = '5'; // 5.7

            break;
        case '10.4':
        case '10.5':
        case '10.6':
            default:
            $mysql_version = '8'; // 8.0

            break;
    }
} else {
    $mysql_version = version_compare($sqlServerVersion ?? '8', '8', '>=')
    ? '8'
    : '5';
}
$dbSchemaVer = sprintf('mysql-%s', $mysql_version);
$paramsCheck = $params ?? [];
unset($paramsCheck['dump']);

$query_populate_stats =
    <<<SQL
    TRUNCATE TABLE `%table_prefix%stats`;
    INSERT INTO `%table_prefix%stats` (stat_id, stat_date_gmt, stat_type)
    VALUES ("1", NULL, "total")
    ON DUPLICATE KEY UPDATE stat_type=stat_type;

    UPDATE `%table_prefix%stats`
    SET stat_images      = (SELECT IFNULL(COUNT(*), 0) FROM `%table_prefix%images`),
        stat_albums      = (SELECT IFNULL(COUNT(*), 0) FROM `%table_prefix%albums`),
        stat_users       = (SELECT IFNULL(COUNT(*), 0) FROM `%table_prefix%users`),
        stat_image_views = (SELECT IFNULL(SUM(image_views), 0) FROM `%table_prefix%images`),
        stat_disk_used   = (SELECT IFNULL(SUM(image_size) + SUM(image_thumb_size) + SUM(image_medium_size), 0)
                            FROM `%table_prefix%images`)
    WHERE stat_type = "total";

    INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_images, stat_image_views, stat_disk_used)
    SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_images, sb.stat_image_views, sb.stat_disk_used
    FROM (SELECT "date"                                                AS stat_type,
                DATE(image_date_gmt)                                   AS stat_date_gmt,
                COUNT(*)                                               AS stat_images,
                SUM(image_views)                                       AS stat_image_views,
                SUM(image_size + image_thumb_size + image_medium_size) AS stat_disk_used
        FROM `%table_prefix%images`
        GROUP BY DATE(image_date_gmt)) AS sb
    ON DUPLICATE KEY UPDATE stat_images = sb.stat_images;

    INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_users)
    SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_users
    FROM (SELECT "date" AS stat_type, DATE(user_date_gmt) AS stat_date_gmt, COUNT(*) AS stat_users
        FROM `%table_prefix%users`
        GROUP BY DATE(user_date_gmt)) AS sb
    ON DUPLICATE KEY UPDATE stat_users = sb.stat_users;

    INSERT INTO `%table_prefix%stats` (stat_type, stat_date_gmt, stat_albums)
    SELECT sb.stat_type, sb.stat_date_gmt, sb.stat_albums
    FROM (SELECT "date" AS stat_type, DATE(album_date_gmt) AS stat_date_gmt, COUNT(*) AS stat_albums
        FROM `%table_prefix%albums`
        GROUP BY DATE(album_date_gmt)) AS sb
    ON DUPLICATE KEY UPDATE stat_albums = sb.stat_albums;

    UPDATE `%table_prefix%users`
    SET user_content_views = COALESCE(
            (SELECT SUM(image_views) FROM `%table_prefix%images` WHERE image_user_id = user_id GROUP BY user_id), "0");
    SQL;

if (isset($installed_version) && empty($paramsCheck)) {
    $db_settings_keys = [];

    try {
        $db_settings = DB::get('settings', 'all');
        foreach ($db_settings as $k => $v) {
            $db_settings_keys[] = $v['setting_name'];
        }
    } catch (Exception $e) {
    }
    if ((
        !empty($db_settings_keys)
            && count($chv_initial_settings) !== count($db_settings_keys)
    ) || (version_compare(APP_VERSION, $installed_version, '>'))
    ) {
        if (!array_key_exists(APP_VERSION, $settings_updates)) {
            throw new LogicException(
                message: message('Outdated installation files. Re-upload %folder% folder with the one from %version%')
                    ->withStrtr('%folder%', 'app/legacy/install')
                    ->withStrtr('%version%', APP_VERSION)
            );
        }
        $schema = [];
        $raw_schema = DB::queryFetchAll('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . env()['CHEVERETO_DB_NAME'] . '" AND TABLE_NAME LIKE "' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '%";');
        foreach ($raw_schema as $k => $v) {
            $TABLE = preg_replace('#' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '#i', '', strtolower($v['TABLE_NAME']), 1);
            $COLUMN = $v['COLUMN_NAME'];
            if (!array_key_exists($TABLE, $schema)) {
                $schema[$TABLE] = [];
            }
            $schema[$TABLE][$COLUMN] = $v;
        }
        $triggers_to_remove = [
            'album_insert',
            'album_delete',
            'follow_insert',
            'follow_delete',
            'image_insert',
            'image_update',
            'image_delete',
            'like_insert',
            'like_delete',
            'notification_insert',
            'notification_update',
            'notification_delete',
            'user_insert',
            'user_delete',
        ];
        $db_triggers = DB::queryFetchAll('SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS');
        if ($db_triggers) {
            $drop_trigger_sql = null;
            foreach ($db_triggers as $k => $v) {
                $trigger = $v['TRIGGER_NAME'];
                if (in_array($v['TRIGGER_NAME'], $triggers_to_remove)) {
                    $drop_trigger = 'DROP TRIGGER IF EXISTS `' . $v['TRIGGER_NAME'] . '`;' . "\n";
                    $drop_trigger_sql .= $drop_trigger;
                }
            }
            if (!is_null($drop_trigger_sql)) {
                $drop_trigger_sql = rtrim($drop_trigger_sql, "\n");
                $remove_triggers = false;
                $remove_triggers = DB::queryExecute($drop_trigger_sql);
                if ($remove_triggers === 0) {
                    chevereto_die('', 'To proceed you will need to run these queries in your database server: <br><br> <textarea class="resize-vertical highlight r5">' . $drop_trigger_sql . '</textarea>', "Can't remove table triggers");
                }
            }
        }
        $DB_indexes = [];
        $raw_indexes = DB::queryFetchAll('SELECT DISTINCT TABLE_NAME, INDEX_NAME, INDEX_TYPE FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = "' . env()['CHEVERETO_DB_NAME'] . '"');
        foreach ($raw_indexes as $k => $v) {
            $TABLE = preg_replace('#' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '#i', '', strtolower($v['TABLE_NAME']), 1);
            $INDEX_NAME = $v['INDEX_NAME'];
            if (!array_key_exists($TABLE, $DB_indexes)) {
                $DB_indexes[$TABLE] = [];
            }
            $DB_indexes[$TABLE][$INDEX_NAME] = $v;
        }
        $CHV_indexes = [];
        foreach (new DirectoryIterator(PATH_APP . 'schemas/' . $dbSchemaVer) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() || !array_key_exists($fileInfo->getBasename('.sql'), $schema)) {
                continue;
            }
            $crate_table = file_get_contents(realpath($fileInfo->getPathname()));
            if (preg_match_all('/(?:(?:FULLTEXT|UNIQUE)\s*)?KEY `(\w+)` \(.*\)/', $crate_table, $matches)) {
                $CHV_indexes[$fileInfo->getBasename('.sql')] = array_combine($matches[1], $matches[0]);
            }
        }
        $engines = [];
        $raw_engines = DB::queryFetchAll('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = "' . env()['CHEVERETO_DB_NAME'] . '" AND TABLE_NAME LIKE "' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '%";');
        $update_table_storage = [];
        foreach ($raw_engines as $k => $v) {
            $TABLE = preg_replace('#' . env()['CHEVERETO_DB_TABLE_PREFIX'] . '#i', '', strtolower($v['TABLE_NAME']), 1);
            $engines[$TABLE] = $v['ENGINE'];
            if ($v['ENGINE'] !== 'InnoDB') {
                $update_table_storage[] = 'ALTER TABLE ' . $v['TABLE_NAME'] . ' ENGINE = InnoDB;';
            }
        }
        if ($update_table_storage !== []) {
            chevereto_die('', '<p>Database table storage engine needs to be updated to InnoDB. Run the following command(s) in your MySQL console:</p><textarea class="resize-vertical highlight r5">' . implode("\n", $update_table_storage) . '</textarea><p>Review <a href="https://dev.mysql.com/doc/refman/8.0/en/converting-tables-to-innodb.html" target="_blank">Converting Tables from MyISAM to InnoDB</a>.</p>', "Convert MyISAM tables to InnoDB");
        }
        $isUtf8mb4 = version_compare($installed_version, '3.12.10', '>');
        if (version_compare($installed_version, '4.0.0-beta.11', '<')) {
            $loginProviders = array_map(
                function (string $value) {
                    return getSetting($value);
                },
                [
                    'facebook' => 'facebook',
                    'twitter' => 'twitter',
                    'google' => 'google',
                    'vk' => 'vk',
                    'facebook_key' => 'facebook_app_id',
                    'facebook_secret' => 'facebook_app_secret',
                    'twitter_key' => 'twitter_api_key',
                    'twitter_secret' => 'twitter_api_secret',
                    'google_key' => 'google_client_id',
                    'google_secret' => 'google_client_secret',
                    'vk_key' => 'vk_client_id',
                    'vk_secret' => 'vk_client_secret',
                ]
            );
            if (hasEncryption()) {
                $loginProviders = encryptValues(
                    [
                        'facebook_key',
                        'facebook_secret',
                        'twitter_key',
                        'twitter_secret',
                        'google_key',
                        'google_secret',
                        'vk_key',
                        'vk_secret',
                    ],
                    $loginProviders
                );
            }
            $queryLoginProviders =
                <<<SQL
                UPDATE `%table_prefix%login_providers`
                SET `login_provider_key_id` = ':key', `login_provider_key_secret` = ':secret', `login_provider_is_enabled` = ':is_enabled'
                WHERE `login_provider_name` = ':provider';
                SQL;
            $loginProvidersBinds = [];
            foreach (['facebook', 'twitter', 'google', 'vk'] as $provider) {
                $loginProvidersBinds[] = [
                    ':provider' => $provider === 'vk' ? 'Vkontakte' : $provider,
                    ':is_enabled' => (int) $loginProviders[$provider],
                    ':key' => addcslashes($loginProviders[$provider . '_key'] ?? '', "'"),
                    ':secret' => addcslashes($loginProviders[$provider . '_secret'] ?? '', "'")
                ];
            }
            $loginUpdateQueries = '';
            foreach ($loginProvidersBinds as $binds) {
                $loginUpdateQueries .= strtr($queryLoginProviders, $binds);
            }
        }
        if (version_compare($installed_version, '4.0.0', '<')) {
            $passwordAlbums = DB::queryFetchAll(
                DB::getQueryWithTablePrefix(
                    <<<SQL
                    SELECT album_id id, album_password password
                    FROM `%table_prefix%albums`
                    WHERE album_password IS NOT NULL;
                    SQL
                )
            );
            $albumUpdateQueries = '';
            foreach ($passwordAlbums as $album) {
                $hashAlbum = password_hash($album['password'], PASSWORD_BCRYPT);
                $albumUpdateQueries .=
                    <<<SQL
                    UPDATE `%table_prefix%albums`
                    SET album_password = '{$hashAlbum}'
                    WHERE album_id = {$album['id']};

                    SQL;
            }
        }
        $update_table = [
            '3.1.0' => [
                'logins' => [
                    'login_resource_id' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'login_secret' => [
                        'op' => 'MODIFY',
                        'type' => $isUtf8mb4 ? 'mediumtext' : 'text', //3.13.0
                        'prop' => "DEFAULT NULL COMMENT 'The secret part'",
                    ],
                ],
                'users' => [
                    'user_name' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'settings' => [
                    'setting_value' => [
                        'op' => 'MODIFY',
                        'type' => $isUtf8mb4 ? 'mediumtext' : 'text', //3.13.0
                        'prop' => null,
                    ],
                    'setting_default' => [
                        'op' => 'MODIFY',
                        'type' => $isUtf8mb4 ? 'mediumtext' : 'text', //3.13.0
                        'prop' => null,
                    ],
                ],
            ],
            '3.3.0' => [
                'albums' => [
                    'album_privacy' => [
                        'op' => 'MODIFY',
                        'type' => "enum('public','password','private','private_but_link','custom')",
                        'prop' => "DEFAULT 'public'",
                    ],
                ],
            ],
            '3.4.0' => [
                'images' => [
                    'image_category_id' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'albums' => [
                    'album_description' => [
                        'op' => 'ADD',
                        'type' => 'text',
                        'prop' => null,
                    ],
                ],
                'categories' => [],
            ],
            '3.5.0' => [
                'images' => [
                    'image_original_exifdata' => [
                        'op' => 'MODIFY',
                        'type' => 'longtext',
                        'prop' => null,
                    ],
                    'image_storage' => [
                        'op' => 'CHANGE',
                        'to' => 'image_storage_mode',
                        'type' => "enum('datefolder','direct','old')",
                        'prop' => "NOT NULL DEFAULT 'datefolder'",
                    ],
                    'image_chain' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(128)',
                        'prop' => 'NOT NULL',
                        'tail' =>
                            <<<SQL
                            UPDATE `%table_prefix%images` set `image_chain` = 7;
                            SQL,
                    ],
                ],
                'storages' => [],
                'storage_apis' => [],
            ],
            '3.5.3' => [
                'storages' => [
                    'storage_region' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.5.5' => [
                'queues' => [],
                'storages' => [
                    'storage_server' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'storage_capacity' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'storage_space_used' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "DEFAULT '0'",
                        'tail' =>
                            <<<SQL
                            UPDATE `%table_prefix%storages` SET storage_space_used = (SELECT SUM(image_size) AS count
                            FROM `%table_prefix%images`
                            WHERE image_storage_id = `%table_prefix%storages`.storage_id);
                            SQL,
                    ],
                ],
                'images' => [
                    'image_thumb_size' => [
                        'op' => 'ADD',
                        'type' => 'int(11)',
                        'prop' => 'NOT NULL',
                    ],
                    'image_medium_size' => [
                        'op' => 'ADD',
                        'type' => 'int(11)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
            ],
            '3.5.7' => [
                'queues' => [
                    'queue_type' => [
                        'op' => 'MODIFY',
                        'type' => "enum('storage-delete')",
                        'prop' => 'NOT NULL',
                        'tail' =>
                            <<<SQL
                            UPDATE `%table_prefix%queues` SET queue_type='storage-delete';
                            SQL,
                    ],
                ],
                'storages' => [
                    'storage_server' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.5.8' => [
                'images' => [
                    'op' => 'ALTER',
                    'prop' => 'ENGINE=%table_engine%; CREATE FULLTEXT INDEX `searchindex` ON `%table_prefix%images`(image_name, image_description, image_original_filename)',
                ],
                'albums' => [
                    'op' => 'ALTER',
                    'prop' => 'ENGINE=%table_engine%; CREATE FULLTEXT INDEX `searchindex` ON `%table_prefix%albums`(album_name, album_description)',
                ],
                'users' => [
                    'op' => 'ALTER',
                    'prop' => 'ENGINE=%table_engine%; CREATE FULLTEXT INDEX `searchindex` ON `%table_prefix%users`(user_name, user_username)',
                ],
            ],
            '3.5.9' => [
                'images' => [
                    'image_title' => [
                        'op' => 'ADD',
                        'type' => 'varchar(100)', // 3.6.5
                        'prop' => 'DEFAULT NULL',
                        'tail' =>
                            <<<SQL
                            DROP INDEX searchindex ON `%table_prefix%images`;
                            UPDATE `%table_prefix%images` SET `image_title` = SUBSTRING(`image_description`, 1, 100);
                            UPDATE `%table_prefix%images` SET image_description = NULL WHERE image_title = image_description;
                            CREATE FULLTEXT INDEX searchindex ON `%table_prefix%images`(image_name, image_title, image_description, image_original_filename);
                            SQL,
                    ],
                ],
                'albums' => [
                    'album_name' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(100)', // 3.6.5
                        'prop' => 'NOT NULL',
                    ],
                ],
            ],
            '3.5.11' => [
                'queues' => [
                    'queue_attempts' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT 0',
                    ],
                    'queue_status' => [
                        'op' => 'ADD',
                        'type' => "enum('pending','failed')",
                        'prop' => "NOT NULL DEFAULT 'pending'",
                    ],
                ],
            ],
            '3.5.12' => [
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%settings` SET `setting_value` = 0, `setting_default` = 0, `setting_typeset` = "bool"
                    WHERE `setting_name` = "maintenance";
                    SQL,
            ],
            '3.5.14' => [
                'ip_bans' => [],
            ],
            '3.6.0' => [
                'users' => [
                    'user_newsletter_subscribe' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '1'",
                    ],
                    'user_show_nsfw_listings' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'user_bio' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.6.2' => [
                'storages' => [
                    'storage_key' => [
                        'op' => 'MODIFY',
                        'type' => $isUtf8mb4 ? 'mediumtext' : 'text', //3.13.0
                        'prop' => null,
                    ],
                    'storage_secret' => [
                        'op' => 'MODIFY',
                        'type' => $isUtf8mb4 ? 'mediumtext' : 'text', //3.13.0
                        'prop' => null,
                    ],
                ],
            ],
            '3.6.3' => [
                'storages' => [
                    'storage_account_id' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'storage_account_name' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'query' =>
                    <<<SQL
                    INSERT IGNORE INTO `%table_prefix%storage_apis` VALUES ('7', 'OpenStack', 'openstack');
                    SQL,
            ],
            '3.6.4' => [
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%settings` SET `setting_value`="mail"
                    WHERE setting_name = "email_mode"
                    AND `setting_value`="phpmail";
                    UPDATE `%table_prefix%settings` SET `setting_default`="mail"
                    WHERE setting_name = "email_mode";
                    SQL,
            ],
            '3.6.5' => [
                'images' => [
                    'image_title' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(100)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'albums' => [
                    'album_name' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(100)',
                        'prop' => 'NOT NULL',
                    ],
                ],
            ],
            '3.6.7' => [
                'pages' => [],
            ],
            '3.6.8' => [
                'users' => [
                    'user_image_keep_exif' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '1'",
                    ],
                    'user_image_expiration' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'images' => [
                    'image_expiration_date_gmt' => [
                        'op' => 'ADD',
                        'type' => 'datetime',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.7.0' => [
                'deletions' => [],
                'follows' => [],
                'likes' => [],
                'notifications' => [],
                'stats' => [],
                'albums' => [
                    'album_creation_ip' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                    'album_likes' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'images' => [
                    'image_likes' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'users' => [
                    'user_registration_ip' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                    'user_likes' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0' COMMENT 'Likes made to content owned by this user'",
                    ],
                    'user_liked' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0' COMMENT 'Likes made by this user'",
                    ],
                    'user_following' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'user_followers' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'user_content_views' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'user_notifications_unread' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'query' => $query_populate_stats,
            ],
            '3.7.5' => [
                'users' => [
                    'user_is_private' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'storages' => [
                    'storage_service' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.8.0' => [
                'images' => [
                    'image_is_animated' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'albums' => [
                    'album_password' => [
                        'op' => 'ADD',
                        'type' => 'text',
                        'prop' => null,
                    ],
                ],
                'requests' => [
                    //'request_type' => [], void in 4.0.0-beta.10
                    'request_content_id' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.9.0' => [
                'albums' => [
                    'album_views' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'likes' => [
                    'like_content_type' => [
                        'op' => 'MODIFY',
                        'type' => "enum('image','album')",
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'notifications' => [
                    'notification_content_type' => [
                        'op' => 'MODIFY',
                        'type' => "enum('user','image','album')",
                        'prop' => 'NOT NULL',
                    ],
                ],
                'stats' => [
                    'stat_album_views' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'stat_album_likes' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                    'stat_likes' => [
                        'op' => 'CHANGE',
                        'to' => 'stat_image_likes',
                        'type' => 'bigint(32)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
            ],
            '3.10.13' => [
                'images' => [
                    'image_source_md5' => [
                        'op' => 'ADD',
                        'type' => 'varchar(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
            ],
            '3.11.0' => [
                'query' =>
                    <<<SQL
                    INSERT INTO `%table_prefix%storage_apis` VALUES ('8', 'Local', 'local') ON DUPLICATE KEY UPDATE storage_api_type = 'local';
                    SQL,
            ],
            '3.12.0' => [
                'imports' => [],
                'importing' => [],
                'images' => [
                    'image_storage_mode' => [
                        'op' => 'MODIFY',
                        'type' => "enum('datefolder','direct','old','path')",
                        'prop' => "NOT NULL DEFAULT 'datefolder'",
                    ],
                    'image_path' => [
                        'op' => 'ADD',
                        'type' => 'varchar(4096)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'albums' => [
                    'album_user_id' => [
                        'op' => 'MODIFY',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'users' => [
                    'user_is_manager' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ],
                ],
                'query' =>
                <<<SQL
                UPDATE `%table_prefix%pages` SET page_icon = "fas fa-landmark" WHERE page_url_key = "tos" AND page_icon IS NULL OR page_icon = "";
                UPDATE `%table_prefix%pages` SET page_icon = "fas fa-lock" WHERE page_url_key = "privacy" AND page_icon IS NULL OR page_icon = "";
                UPDATE `%table_prefix%pages` SET page_icon = "fas fa-at" WHERE page_url_key = "contact" AND page_icon IS NULL OR page_icon = "";
                INSERT INTO `%table_prefix%storage_apis` VALUES ("3", "Microsoft Azure", "azure") ON DUPLICATE KEY UPDATE storage_api_name = "Microsoft Azure";
                INSERT INTO `%table_prefix%storage_apis` VALUES ("9", "S3 compatible", "s3compatible") ON DUPLICATE KEY UPDATE storage_api_type = "s3compatible";
                INSERT INTO `%table_prefix%storage_apis` VALUES ("10", "Alibaba Cloud OSS", "oss") ON DUPLICATE KEY UPDATE storage_api_type = "oss";
                INSERT INTO `%table_prefix%storage_apis` VALUES ("11", "Backblaze B2 (legacy API)", "b2") ON DUPLICATE KEY UPDATE storage_api_type = "b2";
                SQL,
            ],
            '3.12.4' => [
                'pages' => [
                    'page_internal' => [
                        'op' => 'ADD',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%pages` SET page_internal = "tos" WHERE page_url_key = "tos";
                    UPDATE `%table_prefix%pages` SET page_internal = "privacy" WHERE page_url_key = "privacy";
                    UPDATE `%table_prefix%pages` SET page_internal = "contact" WHERE page_url_key = "contact";
                    SQL
            ],
            // 191 because old MyIsam tables. InnoDB 255
            '3.13.0' => [
                'settings' => [
                    'setting_name' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'CHARACTER SET utf8 COLLATE utf8_bin NOT NULL',
                    ],
                ],
                'deletions' => [
                    'deleted_content_ip' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                ],
                'ip_bans' => [
                    'ip_ban_ip' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                    'ip_ban_message' => [
                        'op' => 'MODIFY',
                        'type' => 'text',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'pages' => [
                    'page_internal' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'users' => [
                    'user_username' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                    'user_email' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'user_image_expiration' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'user_registration_ip' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'NOT NULL',
                    ],
                ],
                /*
                    * Note: The change from utf8 to utf8mb4 causes that TEXT changes to MEDIUMTEXT to ensure that the
                    * converted data will fit in the resulting column. Same goes for MEDIUMTEXT -> LONGTEXT
                    *
                    * https://dev.mysql.com/doc/refman/5.7/en/alter-table.html
                    * Look for "For a column that has a data type of VARCHAR or one"
                    */
                'query' => ($DB_indexes['albums']['album_creation_ip'] ? 'DROP INDEX `album_creation_ip` ON `%table_prefix%albums`;
ALTER TABLE `%table_prefix%albums` ADD INDEX `album_creation_ip` (`album_creation_ip`(255));
' : null) . ($DB_indexes['images']['image_name'] ? 'DROP INDEX `image_name` ON `%table_prefix%images`;
ALTER TABLE `%table_prefix%images` ADD INDEX `image_name` (`image_name`(255));
' : null) . ($DB_indexes['images']['image_extension'] ? 'DROP INDEX `image_extension` ON `%table_prefix%images`;
ALTER TABLE `%table_prefix%images` ADD INDEX `image_extension` (`image_extension`(255));
' : null) . ($DB_indexes['images']['image_uploader_ip'] ? 'DROP INDEX `image_uploader_ip` ON `%table_prefix%images`;
ALTER TABLE `%table_prefix%images` ADD INDEX `image_uploader_ip` (`image_uploader_ip`(255));
' : null) . ($DB_indexes['images']['image_uploader_ip'] ? 'DROP INDEX `image_path` ON `%table_prefix%images`;
ALTER TABLE `%table_prefix%images` ADD INDEX `image_path` (`image_path`(255));
' : null) . ($isUtf8mb4 ? null : 'ALTER TABLE `%table_prefix%images` ENGINE=%table_engine%;
ALTER TABLE `%table_prefix%albums` ENGINE=%table_engine%;
ALTER TABLE `%table_prefix%users` ENGINE=%table_engine%;
ALTER TABLE `%table_prefix%albums` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%deletions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%images` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%ip_bans` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%pages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%storages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `%table_prefix%users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'),
            ],
            '3.13.4' => [
                'query' =>
                    <<<SQL
                    ALTER TABLE `%table_prefix%logins` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                    SQL,
            ],
            '3.14.0' => [
                'deletions' => [
                    'deleted_content_original_filename' => [
                        'op' => 'MODIFY',
                        'type' => 'varchar(255)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'logins' => [
                    'login_type' => [
                        'op' => 'MODIFY',
                        'type' => "enum('password','session','cookie','facebook','twitter','google','vk','cookie_facebook','cookie_twitter','cookie_google','cookie_vk')",
                        'prop' => 'NOT NULL',
                    ],
                ],
            ],
            '3.15.0' => [
                'imports' => [
                    'import_continuous' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ]
                ],
                'query' =>
                    <<<SQL
                    INSERT INTO `%table_prefix%imports` (`import_path`, `import_options`, `import_status`, `import_users`, `import_images`, `import_albums`, `import_time_created`, `import_time_updated`, `import_errors`, `import_started`, `import_continuous`)
                    SELECT '%rootPath%importing/no-parse', 'a:1:{s:4:\"root\";s:5:\"plain\";}', 'working', '0', '0', '0', NOW(), NOW(), '0', '0', '1' FROM DUAL
                    WHERE NOT EXISTS (SELECT * FROM `%table_prefix%imports` WHERE `import_path`='%rootPath%importing/no-parse' AND `import_continuous`=1 LIMIT 1);
                    INSERT INTO `%table_prefix%imports` (`import_path`, `import_options`, `import_status`, `import_users`, `import_images`, `import_albums`, `import_time_created`, `import_time_updated`, `import_errors`, `import_started`, `import_continuous`)
                    SELECT '%rootPath%importing/parse-users', 'a:1:{s:4:\"root\";s:5:\"users\";}', 'working', '0', '0', '0', NOW(), NOW(), '0', '0', '1' FROM DUAL
                    WHERE NOT EXISTS (SELECT * FROM `%table_prefix%imports` WHERE `import_path`='%rootPath%importing/parse-users' AND `import_continuous`=1 LIMIT 1);
                    INSERT INTO `%table_prefix%imports` (`import_path`, `import_options`, `import_status`, `import_users`, `import_images`, `import_albums`, `import_time_created`, `import_time_updated`, `import_errors`, `import_started`, `import_continuous`)
                    SELECT '%rootPath%importing/parse-albums', 'a:1:{s:4:\"root\";s:6:\"albums\";}', 'working', '0', '0', '0', NOW(), NOW(), '0', '0', '1' FROM DUAL
                    WHERE NOT EXISTS (SELECT * FROM `%table_prefix%imports` WHERE `import_path`='%rootPath%importing/parse-albums' AND `import_continuous`=1 LIMIT 1);
                    SQL
            ],
            '3.16.0' => [
                'locks' => [],
                'images' => [
                    'image_is_approved' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '1'",
                    ],
                ],
            ],
            '3.17.0' => [
                'albums' => [
                    'album_cover_id' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                    'album_parent_id' => [
                        'op' => 'ADD',
                        'type' => 'bigint(32)',
                        'prop' => 'DEFAULT NULL',
                    ],
                ],
                'images' => [
                    'image_is_360' => [
                        'op' => 'ADD',
                        'type' => 'tinyint(1)',
                        'prop' => "NOT NULL DEFAULT '0'",
                    ]
                ],
                'query' =>
                    <<<SQL
                    UPDATE %table_prefix%albums
                    SET album_cover_id = (SELECT image_id FROM %table_prefix%images WHERE image_album_id = album_id AND image_is_approved = 1 LIMIT 1)
                    WHERE album_cover_id IS NULL;
                    SQL
            ],
            '3.20.0' => [
                'assets' => [],
                'pages' => [
                    'page_code' => [
                        'op' => 'ADD',
                        'type' => 'text',
                        'prop' => null,
                    ]
                ],
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/favicon.png" WHERE `setting_name`="favicon_image" AND `setting_value`="favicon.png";
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/logo.png" WHERE `setting_name`="logo_image" AND `setting_value`="logo.png";
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/logo.svg" WHERE `setting_name`="logo_vector" AND `setting_value`="logo.svg";
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/home_cover.jpg" WHERE `setting_name`="homepage_cover_image" AND `setting_value`="home_cover.jpg";
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/home_cover.jpg" WHERE `setting_name`="homepage_cover_image" AND `setting_value` IS NULL;
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/logo_homepage.png" WHERE `setting_name`="logo_image_homepage" AND `setting_value`="logo_homepage.png";
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/logo_homepage.svg" WHERE `setting_name`="logo_vector_homepage" AND `setting_value`="logo_homepage.svg";
                    SQL
            ],
            '3.20.1' => [
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%settings` SET `setting_value`="default/consent-screen_cover.jpg"
                    WHERE `setting_name`="consent_screen_cover_image"
                    AND `setting_value` IS NULL;
                    SQL
            ],
            '3.20.2' => [
                'query' =>
                    <<<SQL
                    ALTER TABLE `%table_prefix%importing` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                    ALTER TABLE `%table_prefix%imports` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                    SQL
            ],
            '3.20.4' => [
                'query' => 'UPDATE `%table_prefix%settings` SET `setting_value`="default/watermark.png" WHERE `setting_name`="watermark_image" AND `setting_value`="watermark.png";'
            ],
            '3.20.5' => [
                'query' => 'UPDATE `%table_prefix%settings` SET `setting_typeset`="string" WHERE `setting_name`="explore_albums_min_image_count";',
            ],
            '3.20.6' => [
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%pages` SET `page_icon`="fas fa-landmark" WHERE `page_icon`="icon-text";
                    UPDATE `%table_prefix%pages` SET `page_icon`="fas fa-lock" WHERE `page_icon`="icon-lock";
                    UPDATE `%table_prefix%pages` SET `page_icon`="fas fa-at" WHERE `page_icon`="icon-mail";
                    SQL,
            ],
            '4.0.0.beta.5' => [
                'users' => [
                    'user_palette_id' => [
                        'op' => 'ADD',
                        'type' => 'int(11)',
                        'prop' => "NOT NULL DEFAULT '0'"
                    ],
                ],
                'query' => version_compare($installed_version, '3.15.0', '>=')
                    ? 'ALTER TABLE `%table_prefix%users` DROP COLUMN `user_is_dark_mode`;'
                    : '',
            ],
            '4.0.0.beta.7' => [
                'api_keys' => [],
                'images_hash' => [],
            ],
            '4.0.0-beta.10' => [
                'two_factors' => [],
                'requests' => [
                    'request_type' => [
                        'op' => 'MODIFY',
                        'type' => "enum('upload','signup','account-edit','account-password-forgot','account-password-reset','account-resend-activation','account-email-needed','account-change-email','account-activate','login','content-password','account-two-factor')",
                        'prop' => 'NOT NULL',
                    ],
                ]
            ],
            '4.0.0-beta.11' => [
                'pages' => [
                    'page_code' => [
                        'op' => 'MODIFY',
                        'type' => "mediumtext",
                        'prop' => null,
                    ],
                ],
                'login_connections' => [],
                'login_cookies' => [],
                'login_passwords' => [],
                'login_providers' => [],
                'query' =>
                    <<<SQL
                    INSERT IGNORE INTO `%table_prefix%login_passwords` (login_password_user_id, login_password_date_gmt, login_password_hash)
                    SELECT login_user_id, max(login_date_gmt), login_secret
                    FROM `%table_prefix%logins`
                    WHERE login_type = "password"
                    GROUP BY login_user_id;
                    INSERT IGNORE INTO `%table_prefix%login_cookies` (login_cookie_user_id, login_cookie_connection_id, login_cookie_date_gmt,
                                                               login_cookie_ip, login_cookie_user_agent, login_cookie_hash)
                    SELECT login_user_id, 0, login_date_gmt, login_ip, login_hostname, login_secret
                    FROM `%table_prefix%logins`
                    WHERE login_type = "cookie"
                    GROUP BY login_date_gmt
                    ORDER BY login_date_gmt DESC;
                    INSERT IGNORE INTO `%table_prefix%login_connections` (login_connection_user_id, login_connection_provider_id, login_connection_date_gmt,
                                                                   login_connection_resource_id, login_connection_resource_name,
                                                                   login_connection_token)
                    SELECT login_user_id, login_provider_id, max(login_date_gmt), login_resource_id, login_resource_name, '' token
                    FROM `%table_prefix%logins`
                            JOIN `%table_prefix%login_providers` ON login_provider_name = login_type COLLATE utf8mb4_unicode_ci
                    WHERE login_type IN ('facebook', 'twitter', 'google', 'vk')
                    GROUP BY login_user_id, login_provider_id;
                    SQL
                    .
                    "\n"
                    . ($loginUpdateQueries ?? ''),
            ],
            '4.0.0' => [
                'query' => $albumUpdateQueries ?? '',
            ],
            '4.0.1' => [
                'query' =>
                    <<<SQL
                    UPDATE `%table_prefix%settings`
                    SET setting_typeset = 'string',
                        setting_default = ''
                    WHERE setting_name = 'auto_delete_guest_uploads';
                    SQL,
            ],
        ];
        $sql_update = [];
        if (!$maintenance) {
            $sql_update[] = "UPDATE `%table_prefix%settings` SET `setting_value` = 1 WHERE `setting_name` = 'maintenance';";
        }
        $required_sql_files = [];
        foreach ($update_table as $version => $changes) {
            foreach ($changes as $table => $columns) {
                if ($table == 'query') {
                    continue;
                }
                $schema_table = $schema[$table] ?? [];
                $create_table = false;
                if (!array_key_exists($table, $schema) && !in_array($table, $required_sql_files)) {
                    $create_table = true;
                } elseif ($table == 'storages' && !array_key_exists('storage_bucket', $schema_table)) {
                    $create_table = true;
                }
                if (!in_array($table, $required_sql_files) && $create_table) {
                    $sql_update[] = file_get_contents(PATH_APP . 'schemas/' . $dbSchemaVer . '/' . $table . '.sql');
                    $required_sql_files[] = $table;
                }
                if (in_array($table, $required_sql_files)) {
                    continue;
                }
                if (isset($columns['op'])) {
                    if ($columns['op'] === 'ALTER') {
                        if ($DB_indexes[$table]['searchindex'] && strpos($columns['prop'], 'CREATE FULLTEXT INDEX `searchindex`') !== false) {
                            continue 2;
                        }
                        $sql_update[] = strtr(
                            'ALTER TABLE `%table_prefix%' . $table . '` %prop; %tail',
                            [
                                '%prop' => $columns['prop'],
                                '%tail' => $columns['tail'] ?? ''
                            ]
                        );
                    }

                    continue;
                }
                foreach ($columns as $column => $column_meta) {
                    $query = null;
                    $schema_column = $schema_table[$column] ?? null;
                    switch ($column_meta['op']) {
                        case 'MODIFY':
                            if (
                                array_key_exists($column, $schema[$table])
                                && (
                                    $schema_column['COLUMN_TYPE'] !== $column_meta['type']
                                    || preg_match('/DEFAULT NULL/i', $column_meta['prop'] ?? '')
                                    && $schema_column['IS_NULLABLE'] == 'NO'
                                )
                            ) {
                                $query = '`%column` %type';
                            }

                            break;
                        case 'CHANGE':
                            if (array_key_exists($column, $schema[$table])) {
                                $query = '`%column` `%to` %type';
                            }

                            break;
                        case 'ADD':
                            if (!array_key_exists($column, $schema[$table])) {
                                $query = '`%column` %type';
                            }

                            break;
                    }
                    if (!is_null($query)) {
                        $stock_tr = ['op', 'type', 'to', 'prop', 'tail'];
                        $meta_tr = [];
                        foreach ($stock_tr as $v) {
                            $meta_tr['%' . $v] = $column_meta[$v] ?? '';
                        }
                        $sql_update[] = strtr(
                            'ALTER TABLE `%table_prefix%' . $table . '` %op ' . $query . ' %prop; %tail',
                            array_merge(['%column' => $column], $meta_tr)
                        );
                    }
                }
            }
            if (isset($changes['query']) && version_compare($version, $installed_version, '>')) {
                $sql_update[] = $changes['query'];
            }
        }
        foreach ($CHV_indexes as $table => $indexes) {
            $field_prefix = DB::getFieldPrefix($table);
            foreach ($indexes as $index => $indexProp) {
                if ($index == 'searchindex' || $index == $field_prefix . '_id' || !starts_with($field_prefix . '_', $index)) {
                    continue;
                }
                if (!array_key_exists($index, $DB_indexes[$table])) {
                    $sql_update[] = 'ALTER TABLE `%table_prefix%' . $table . '` ADD ' . $indexProp . ';';
                }
            }
        }
        $settings_flat = [];
        foreach (array_keys(array_merge($settings_updates, $update_table)) as $k) {
            $sql = null;
            if (is_array($settings_updates[$k])) {
                foreach ($settings_updates[$k] as $k => $v) {
                    $settings_flat[$k] = $v;
                    if (in_array($k, $db_settings_keys)) {
                        continue;
                    }
                    $value = (is_null($v) ? 'NULL' : "'" . $v . "'");
                    $sql .= "INSERT INTO `%table_prefix%settings` (setting_name, setting_value, setting_default, setting_typeset) VALUES ('" . $k . "', " . $value . ', ' . $value . ", '" . Settings::getType($v) . "'); " . "\n";
                }
            }
            if ($sql !== null) {
                $sql_update[] = $sql;
            }
        }
        foreach ($settings_delete as $k) {
            if (array_key_exists($k, Settings::get())) {
                $sql_update[] = "DELETE FROM `%table_prefix%settings` WHERE `setting_name` = '$k';";
            }
        }
        foreach ($settings_rename as $k => $v) {
            if (array_key_exists($k, Settings::get())) {
                $settingValue = Settings::get()[$k];
                $value = is_null($settingValue)
                    ? 'NULL'
                    : "'$settingValue'";
                $sql_update[] = <<<SQL
                UPDATE `%table_prefix%settings` SET `setting_value` = $value WHERE `setting_name` = '$v';
                DELETE FROM `%table_prefix%settings` WHERE `setting_name` = '$k';
                SQL;
            }
        }
        $sql_update[] = 'UPDATE `%table_prefix%settings` SET `setting_value` = "' . APP_VERSION . '" WHERE `setting_name` = "chevereto_version_installed";';
        if (!$maintenance) {
            $sql_update[] = 'UPDATE `%table_prefix%settings` SET `setting_value` = 0 WHERE `setting_name` = "maintenance";';
        }
        $sql_update = implode("\r\n", $sql_update);
        $sql_update = strtr($sql_update, [
            '%rootPath%' => PATH_PUBLIC,
            '%table_prefix%' => env()['CHEVERETO_DB_TABLE_PREFIX'],
            '%table_engine%' => $fulltext_engine,
        ]);
        $sql_update = preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', "\n", $sql_update));
        $isDumpUpdate = Settings::get('dump_update_query');
        if (($params['dump'] ?? null) === true) {
            $isDumpUpdate = true;
        }
        if (!$isDumpUpdate && PHP_SAPI !== 'cli') {
            $totalDb = DB::get('stats', ['type' => 'total'])[0] ?? null;
            $totalImages = (int) ($totalDb['stat_images'] ?? 0);
            $stopWordsRegex = '/add|alter|modify|change/i';
            $slowUpdate = preg_match_all($stopWordsRegex, $sql_update);
            if ($slowUpdate && $totalImages >= 1000000) {
                $sql_update = '# To protect your DB is mandatory to run this MySQL script directly in your database console.'
                    . "\n" . '# Database:' . env()['CHEVERETO_DB_NAME'] . "\n" . $sql_update;
                $isDumpUpdate = true;
            }
        }
        if ($isDumpUpdate) {
            $dumpMessage = '# Dumped update query (to manually run in the database console)'
                . "\n\n"
                . $sql_update;
            debug($dumpMessage);
            xr($dumpMessage);
            die();
        }
        $updateMessageWrap = PHP_SAPI === 'cli'
            ? <<<CLI
              ---
              %query%
              ---
              CLI
            : getPreCodeHtml('%query%');
        $errorMessageWrap = PHP_SAPI === 'cli'
            ? '>>> %error%'
            : getPreCodeHtml('>>> %error%');
        $db = DB::getInstance();
        $db->query($sql_update);
        xr($sql_update);

        try {
            $updated = $db->exec();
            if ($updated) {
                $chevereto_version_installed = DB::get('settings', ['name' => 'chevereto_version_installed'])[0]['setting_value'];
                if (APP_VERSION !== $chevereto_version_installed) {
                    throw new LogicException(
                        message('Version mismatch.')
                    );
                }
            }
        } catch (Throwable $e) {
            throw new LogicException(
                message(
                    <<<MESSAGE
                    Error executing the Chevereto update query.
                    $errorMessageWrap
                    Try running each of the following statements in the database console to find the conflict.
                    $updateMessageWrap
                    MESSAGE
                )
                    ->withStrtr('%query%', $sql_update)
                    ->withStrtr('%error%', $e->getMessage())
            );
        }
        if ($updated) {
            $itWasUpdated = true;
        }
        $doing = 'updated';
    } else {
        try {
            $db = DB::getInstance();
        } catch (Exception $e) {
            $error = true;
            $error_message = sprintf($db_conn_error, $e->getMessage());
        }
        $doing = $error ? 'connect' : 'ready';

        if ($installed_version !== null) {
            $itWasUpdated = false;
            $doing = 'already';
        }
    }
}
$input_errors = [];
if (!isset($installed_version) && !empty($params)) {
    if (isset($params['username']) && !in_array($doing, ['already', 'update'])) {
        $doing = 'ready';
    }
    switch ($doing) {
        case 'connect':
            $db_details = [];
            foreach ($db_array as $k => $v) {
                if ($v && $params[$k] == '') {
                    $error = true;

                    break;
                }
                $db_details[ltrim($k, 'db_')] = $params[$k] ?? null;
            }
            if ($error) {
                $error_message = 'Please fill the database details.';
            } else {
                $db_details['driver'] = 'mysql';

                try {
                    $db_details['port'] = (int) ($db_details['port'] ?? 3306);
                    $db_details['pdoAttrs'] = [];
                    $db = new DB(...$db_details);
                } catch (Exception $e) {
                    $error = true;
                    $error_message = sprintf($db_conn_error, $e->getMessage());
                }
                if (!$error) {
                    $env = [];
                    $env['%encryptionKey%'] = randomKey()->base64();
                    foreach ($db_details as $k => $v) {
                        $env["%$k%"] = $v;
                    }
                    $dotenvTemplate = <<<EOT
<?php

return [
    'CHEVERETO_DB_HOST' => '%host%',
    'CHEVERETO_DB_NAME' => '%name%',
    'CHEVERETO_DB_PASS' => '%pass%',
    'CHEVERETO_DB_PORT' => '%port%',
    'CHEVERETO_DB_USER' => '%user%',
    'CHEVERETO_DB_TABLE_PREFIX' => '%tablePrefix%',
    'CHEVERETO_ENCRYPTION_KEY' => '%encryptionKey%',
];

EOT;
                    $envDotPhpContents = strtr($dotenvTemplate, $env);

                    try {
                        $envDotPhp = filePhpForPath(PATH_APP . 'env.php');
                        $envDotPhp->file()->removeIfExists();
                        $envDotPhp->file()->create();
                        $envDotPhp->file()->put($envDotPhpContents);
                        $doing = 'ready';
                    } catch (Throwable $e) {
                        $doing = 'env';
                    }
                }
                if ($doing == 'ready') {
                    redirect('install');
                }
            }

            break;
        case 'ready':
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                $input_errors['email'] = _s('Invalid email');
            }
            if (!User::isValidUsername($params['username'])) {
                $input_errors['username'] = _s('Invalid username');
            }
            if (!preg_match('/' . getSetting('user_password_pattern') . '/', $params['password'] ?? '')) {
                $input_errors['password'] = _s('Invalid password');
            }
            if (count($input_errors) > 0) {
                $error = true;
                $error_message = 'Please correct your data to continue.';
            } else {
                $create_table = [];
                foreach (new DirectoryIterator(PATH_APP . 'schemas/' . $dbSchemaVer) as $fileInfo) {
                    if ($fileInfo->isDot() || $fileInfo->isDir()) {
                        continue;
                    }
                    $create_table[$fileInfo->getBasename('.sql')] = realpath($fileInfo->getPathname());
                }
                $install_sql =
                    <<<SQL
                    SET FOREIGN_KEY_CHECKS=0;
                    SQL;
                if ($is_2X) {
                    // Need to sync this to avoid bad datefolder mapping due to MySQL time != PHP time
                    // In Chevereto v2.X date was TIMESTAMP and in v3.X is DATETIME
                    $DateTime = new DateTime();
                    $offset = $DateTime->getOffset();
                    $offsetHours = round(abs($offset) / 3600);
                    $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
                    $offset = ($offset < 0 ? '-' : '+')
                        . (strlen((string) $offsetHours) < 2 ? '0' : '')
                        . $offsetHours
                        . ':'
                        . (strlen((string) $offsetMinutes) < 2 ? '0' : '')
                        . $offsetMinutes;
                    $install_sql .=
                        <<<SQL
                        SET time_zone = '" . $offset . "';
                        ALTER TABLE `chv_images`
                        MODIFY `image_id` bigint(32) NOT NULL AUTO_INCREMENT,
                        MODIFY `image_name` varchar(255),
                        MODIFY `image_date` DATETIME,
                        CHANGE `image_type` `image_extension` varchar(255),
                        CHANGE `uploader_ip` `image_uploader_ip` varchar(255),
                        CHANGE `storage_id` `image_storage_id` bigint(32),
                        DROP `image_delete_hash`,
                        ADD `image_date_gmt` datetime NOT NULL AFTER `image_date`,
                        ADD `image_title` varchar(100) NOT NULL,
                        ADD `image_description` text,
                        ADD `image_nsfw` tinyint(1) NOT NULL DEFAULT '0',
                        ADD `image_user_id` bigint(32) DEFAULT NULL,
                        ADD `image_album_id` bigint(32) DEFAULT NULL,
                        ADD `image_md5` varchar(32) NOT NULL,
                        ADD `image_source_md5` varchar(32) DEFAULT NULL,
                        ADD `image_storage_mode` enum('datefolder','direct','old') NOT NULL DEFAULT 'datefolder',
                        ADD `image_original_filename` text NOT NULL,
                        ADD `image_original_exifdata` longtext,
                        ADD `image_views` bigint(32) NOT NULL DEFAULT '0',
                        ADD `image_category_id` bigint(32) DEFAULT NULL,
                        ADD `image_chain` tinyint(128) NOT NULL,
                        ADD `image_thumb_size` int(11) NOT NULL,
                        ADD `image_medium_size` int(11) NOT NULL DEFAULT '0',
                        ADD `image_expiration_date_gmt` datetime DEFAULT NULL,
                        ADD `image_likes` bigint(32) NOT NULL DEFAULT '0',
                        ADD `image_is_animated` tinyint(1) NOT NULL DEFAULT '0',
                        ADD INDEX `image_name` (`image_name`),
                        ADD INDEX `image_size` (`image_size`),
                        ADD INDEX `image_width` (`image_width`),
                        ADD INDEX `image_height` (`image_height`),
                        ADD INDEX `image_date_gmt` (`image_date_gmt`),
                        ADD INDEX `image_nsfw` (`image_nsfw`),
                        ADD INDEX `image_user_id` (`image_user_id`),
                        ADD INDEX `image_album_id` (`image_album_id`),
                        ADD INDEX `image_storage_id` (`image_storage_id`),
                        ADD INDEX `image_md5` (`image_md5`),
                        ADD INDEX `image_source_md5` (`image_source_md5`),
                        ADD INDEX `image_likes` (`image_views`),
                        ADD INDEX `image_views` (`image_views`),
                        ADD INDEX `image_category_id` (`image_category_id`),
                        ADD INDEX `image_expiration_date_gmt` (`image_expiration_date_gmt`),
                        ADD INDEX `image_is_animated` (`image_is_animated`),
                        ENGINE=$fulltext_engine;

                        UPDATE `chv_images`
                            SET `image_date_gmt` = `image_date`,
                            `image_storage_mode` = CASE
                            WHEN `image_storage_id` IS NULL THEN 'datefolder'
                            WHEN `image_storage_id` = 0 THEN 'datefolder'
                            WHEN `image_storage_id` = 1 THEN 'old'
                            WHEN `image_storage_id` = 2 THEN 'direct'
                            END,
                            `image_storage_id` = NULL;

                        CREATE FULLTEXT INDEX searchindex ON `chv_images`(image_name, image_title, image_description, image_original_filename);

                        RENAME TABLE `chv_info` to `_chv_info`;
                        RENAME TABLE `chv_options` to `_chv_options`;
                        RENAME TABLE `chv_storages` to `_chv_storages`;
                        SQL;
                    unset($create_table['images']);
                    $chv_initial_settings['crypt_salt'] = $params['crypt_salt'];
                    $table_prefix = 'chv_';
                } else {
                    $table_prefix = env()['CHEVERETO_DB_TABLE_PREFIX'];
                }
                foreach ($create_table as $k => $v) {
                    $install_sql .= strtr(file_get_contents($v), [
                        '%rootPath%' => PATH_PUBLIC,
                        '%table_prefix%' => $table_prefix,
                        '%table_engine%' => $fulltext_engine,
                    ]) . "\n\n";
                }
                $chv_initial_settings['id_padding'] = $is_2X ? 0 : 5000;
                $install_sql .= strtr($query_populate_stats, [
                    '%rootPath%' => PATH_PUBLIC,
                    '%table_prefix%' => $table_prefix,
                    '%table_engine%' => $fulltext_engine,
                ]);
                // $params['dump'] = true;
                if (($params['dump'] ?? false) === true) {
                    debug($install_sql);
                    logger("\n");
                    die(0);
                }
                $db = DB::getInstance();
                $db->query($install_sql);
                $db->exec();
                $db->closeCursor();
                Settings::insert($chv_initial_settings);
                $insert_admin = User::insert([
                    'username' => $params['username'],
                    'email' => $params['email'],
                    'is_admin' => 1,
                    'language' => $chv_initial_settings['default_language'],
                    'timezone' => $chv_initial_settings['default_timezone'],
                ]);
                Login::addPassword($insert_admin, $params['password']);
                $doing = 'finished';
            }

            break;
    }
}
if (PHP_SAPI === 'cli') {
    switch ($doing) {
        case 'already':
            logger(
                ($itWasUpdated ?? false)
                    ? "[NOTICE] Chevereto is already installed\n"
                    : "[NOTICE] Chevereto is already updated\n"
            );

            break;
        case 'finished':
            logger("[OK] Chevereto has been installed\n");

            break;
        case 'updated':
            logger(
                ($itWasUpdated ?? false)
                    ? "[OK] Chevereto database has been updated\n"
                    : "[NOTICE] Chevereto is already updated\n"
            );

            break;
        case 'update_failed':
            logger("[ERROR] Chevereto database update failure\n");
            die(255);
    }
} else {
    $doctitle = $doctitles[$doing] . ' - Chevereto ' . get_chevereto_version(true);
    $system_template = PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'template.php';
    $install_template = PATH_APP_LEGACY_INSTALL . 'template/' . $doing . '.php';
    ob_start();
    require_once $install_template;
    $html = ob_get_contents();
    ob_end_clean();
    require_once $system_template;
}
die(0);
