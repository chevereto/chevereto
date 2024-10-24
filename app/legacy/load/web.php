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
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Fonts;
use Chevereto\Legacy\Classes\IpBan;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Page;
use Chevereto\Legacy\Classes\Palettes;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\Tag;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\badgePaid;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\editionCombo;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\is_route_available;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\set_status_header;
use function Chevereto\Legacy\get_enabled_languages;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSystemNotices;
use function Chevereto\Legacy\getVariable;
use function Chevereto\Legacy\headersNoCache;
use function Chevereto\Legacy\headersResetCache;
use function Chevereto\Legacy\is_max_invalid_request;
use function Chevereto\Vars\cookie;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

if (cheveretoVersionInstalled() === '') {
    new Handler(
        loadTemplate: ! REPL, // @phpstan-ignore-line
        before: function ($handler) {
            headersNoCache();
            if ($handler->request_array()[0] !== 'install') {
                redirect('install', 302);
            }
        },
    );
}
$bannedIp = IpBan::getSingle();
if ($bannedIp !== []) {
    headersNoCache();
    // TODO: Cache until ban expires
    if (is_url($bannedIp['message'] ?? false)) {
        redirect($bannedIp['message'], 301);
    } else {
        $exitMessage = $bannedIp['message'] ?? '';
        $exitMessage = match ($exitMessage) {
            '' => _s('You have been forbidden to use this website.'),
            default => $bannedIp['message'],
        };
        exit($exitMessage);
    }
}
$hook_before = function (Handler $handler) {
    header('Permissions-Policy: unload=()');
    header('Permissions-Policy: interest-cohort=()');
    header("Content-Security-Policy: frame-ancestors 'none'");
    $exitEarlyRoutes = [
        'webmanifest',
    ];
    $doNotCacheRoutes = [
        'login',
        'signup',
        'logout',
        'account',
        'connect',
        'json',
        'api',
        'captcha-verify',
        'oembed',
        'upload',
        'dashboard',
        'install',
        'settings',
        'redirect',
    ];
    $cache_ttl = (int) max(0, getSetting('cache_ttl') ?? 0);
    if (in_array($handler->request_array()[0], $doNotCacheRoutes, true)) {
        headersNoCache();
    } elseif ($cache_ttl > 0) {
        headersResetCache();
        header("Cache-Control: private, max-age={$cache_ttl}");
    }
    if (in_array($handler->request_array()[0], $exitEarlyRoutes, true)) {
        return;
    }
    $failed_access_requests = RequestLog::getCounts(['login', 'signup'], 'fail');
    if (is_max_invalid_request($failed_access_requests['day'])) {
        set_status_header(403);
    } else {
        Login::tryLogin();
    }
    $user_cookie_lang = cookie()['USER_SELECTED_LANG'] ?? null;
    $user_lang = $user_cookie_lang ?? getSetting('default_language');
    if (Login::isLoggedUser()) {
        $user_lang = Login::getUser()['language'];
        if (Login::getUser()['status'] === 'banned') {
            set_status_header(403);
        }
        if (sessionVar()->hasKey('challenge_two_factor')
            && ! in_array($handler->getRoutePath(), ['account/two-factor', 'captcha-verify', 'logout'], true)
            && $handler->request_array()[0] !== 'page'
        ) {
            headersNoCache();
            redirect('account/two-factor', 302);
        }
    }
    if (! getSetting('language_chooser_enable')) {
        $user_lang = getSetting('default_language');
    }
    new L10n(
        $user_lang,
        (Login::isLoggedUser() || $user_cookie_lang !== null)
            ? false
            : getSetting('auto_language')
    );
    if (http_response_code() === 403) {
        headersNoCache();
        exit();
    }
    if ($handler->request_array()[0] !== 'api'
        && Settings::get('enable_uploads_url') && ! Login::isAdmin()) {
        Settings::setValue('enable_uploads_url', 0);
    }
    if (isset(get()['lang'])
        && array_key_exists(get()['lang'], get_enabled_languages())
    ) {
        L10n::setCookieLang(get()['lang']);
        L10n::processTranslation(get()['lang']);
        define('PUSH_LANG', get()['lang']);
    }
    if (array_key_exists('agree-consent', get())) {
        setcookie(
            'AGREE_CONSENT',
            '1',
            time() + (60 * 60 * 24 * 30),
            Config::host()->hostnamePath()
        );
        sessionVar()->put('agree-consent', true);
        headersNoCache();
        redirect(get_current_url(true, ['agree-consent']), 302);
    }
    $base = $handler::baseRequest();
    parse_str(server()['QUERY_STRING'] ?? '', $querystr);
    $handler::setVar('auth_token', $handler::getAuthToken());
    $handler::setVar('doctitle', getSetting('website_name'));
    $handler::setVar('meta_description', getSetting('website_description'));
    $handler::setVar('logged_user', Login::getUser());
    $handler::setVar('failed_access_requests', $failed_access_requests);
    $handler::setVar('header_logo_link', get_base_url());
    $handler::setCond('admin', Login::isAdmin());
    $handler::setCond('manager', Login::isManager());
    $showContentManager = Login::isAdmin() || Login::isManager();
    $handler::setCond('content_manager', $showContentManager);
    $allowed_nsfw_flagging = ! getSetting('image_lock_nsfw_editing');
    if ($handler::cond('content_manager')) {
        $moderateLink = get_base_url('moderate');
        $moderateLabel = _s('Moderate');
        if (! in_array('pro', editionCombo()[env()['CHEVERETO_EDITION']], true)) {
            if ((bool) env()['CHEVERETO_ENABLE_EXPOSE_PAID_FEATURES']) {
                $moderateLink = 'https://chevereto.com/pricing';
                $moderateLabel .= ' ' . badgePaid('pro');
            } else {
                $showContentManager = false;
            }
        }
        $handler::setVar('moderate_link', $moderateLink);
        $handler::setVar('moderate_label', $moderateLabel);
        $allowed_nsfw_flagging = true;
    }
    $handler::setCond('show_content_manager', $showContentManager);
    $handler::setCond('allowed_nsfw_flagging', $allowed_nsfw_flagging);
    $handler::setCond('maintenance', getSetting('maintenance') and ! Login::isAdmin());
    $handler::setCond(
        'show_consent_screen',
        $base !== 'api' && (
            getSetting('enable_consent_screen')
            ? ! (Login::getUser() || isset(session()['agree-consent']) || isset(cookie()['AGREE_CONSENT']))
            : false
        )
    );
    $handler::setCond('captcha_needed', getSetting('captcha') && getSetting('captcha_threshold') === 0);
    $handler::setCond('show_header', ! ($handler::cond('maintenance') || $handler::cond('show_consent_screen')));
    $handler::setCond('show_notifications', getSetting('website_mode') === 'community' && (getSetting('enable_followers') || getSetting('enable_likes')));
    $handler::setCond('allowed_to_delete_content', Login::isAdmin() || getSetting('enable_user_content_delete'));
    $handler::setVar('canonical', null);
    $palettes = new Palettes();
    $handler::setVar('palettes', $palettes);
    $fonts = new Fonts();
    $handler::setVar('fonts', $fonts);
    $fontId = intval(getSetting('theme_font') ?? 0);
    $handler::setVar('theme_font', $fontId);
    if (in_array($handler->request_array()[0], ['login', 'signup', 'account'], true)) {
        $paletteId = 0;
    } else {
        $paletteId = Login::isLoggedUser()
            ? Login::getUser()['palette_id']
            : Settings::get('theme_palette');
    }
    $theme_palette_handle = $palettes->getHandle(intval($paletteId));
    if ($theme_palette_handle === '') {
        $paletteId = 1;
        $theme_palette_handle = $palettes->getHandle(intval($paletteId));
    }
    $handler::setVar('theme_palette', $paletteId);
    $handler::setVar('theme_palette_handle', $theme_palette_handle);
    if ($handler::cond('maintenance')
        && $handler->request_array()[0] === 'dashboard') {
        headersNoCache();
        redirect('login', 302);
    }
    $langLinks = [];
    $langToggleUrl = get_current_url(true, ['lang']);
    parse_str(server()['QUERY_STRING'] ?? '', $qs);
    unset($qs['lang']);
    $qs = http_build_query($qs);
    $langLinks['x-default'] = [
        'hreflang' => 'x-default',
        'name' => 'x-default',
        'url' => get_public_url($langToggleUrl),
    ];
    $langToggleUrl = rtrim($langToggleUrl, '/') . ($qs ? '&' : '/?') . 'lang=';
    foreach (get_enabled_languages() as $k => $v) {
        $hreflang = strtolower($k);
        $langUrl = $langToggleUrl . $k;
        $langLinks[$k] = [
            'hreflang' => $hreflang,
            'name' => $v['name'],
            'url' => get_public_url($langUrl),
        ];
    }
    $handler::setVar('langLinks', $langLinks);
    if ($handler::cond('show_consent_screen')) {
        $hasQs = parse_url(get_current_url(), PHP_URL_QUERY) !== null;
        $consent_accept_url = get_current_url()
            . ($hasQs ? '&' : '/?')
            . 'agree-consent';
        $consent_accept_url = '/' . ltrim($consent_accept_url, '/');
        $handler::setVar(
            'consent_accept_url',
            $consent_accept_url
        );
    }
    if (! Login::getUser()) {
        if (getSetting('captcha') && $failed_access_requests['day'] >= getSetting('captcha_threshold')) {
            $handler::setCond('captcha_needed', true);
        }
    }
    if (getSetting('website_mode') === 'personal') {
        $userMapPaths = ['search'];
        $userMapPaths[] = getSetting('user_profile_view') === 'files'
            ? 'albums'
            : 'files';
        if ($handler->request_array()[0] === '/'
            && getSetting('website_mode_personal_routing') === '/'
            && in_array(key($querystr), ['random'], true)
        ) {
            $handler->mapRoute('index');
        } elseif ($handler->request_array()[0] === 'search'
            && in_array($handler->request_array()[1] ?? [], ['images', 'albums', 'users'], true)
        ) {
            $handler->mapRoute('search');
        } elseif ($handler->request_array()[0] === getSetting('website_mode_personal_routing')
            || (getSetting('website_mode_personal_routing') === '/'
            && in_array($handler->request_array()[0], $userMapPaths, true))
        ) {
            $handler->mapRoute('user', [
                'id' => getSetting('website_mode_personal_uid'),
            ]);
        }
        if ($handler->request_array()[0] === '/'
            && ! in_array(key($querystr), ['random', 'lang'], true)
            && ! $handler::cond('mapped_route')
        ) {
            $personal_mode_user = User::getSingle(getSetting('website_mode_personal_uid'));
            if ($personal_mode_user !== []) {
                if (Settings::get('homepage_cta_html') === null) {
                    Settings::setValue('homepage_cta_html', _s('View all my images'));
                }
                if (Settings::get('homepage_title_html') === null) {
                    Settings::setValue('homepage_title_html', $personal_mode_user['name']);
                }
                if (Settings::get('homepage_paragraph_html') === null) {
                    Settings::setValue('homepage_paragraph_html', _s('Feel free to browse and discover all my shared images and albums.'));
                }
                if (Settings::get('homepage_cta_fn') !== 'cta-link') {
                    Settings::setValue('homepage_cta_fn', 'cta-link');
                    Settings::setValue('homepage_cta_fn_extra', $personal_mode_user['url']);
                }
                if ($personal_mode_user['background']['url'] ?? false) {
                    Settings::setValue('homepage_cover_image', $personal_mode_user['background']['url']);
                }
            }
        }
    } else {
        if ($base !== 'index' and ! is_route_available($handler->request_array()[0])) {
            $mapTo = getSetting('root_route');
            $handler->mapRoute($mapTo);
        }
    }
    $virtual_routes = ['image', 'album', 'user', 'video', 'audio'];
    if (in_array($handler->request_array()[0], $virtual_routes, true)) {
        $virtual_route = getSetting('route_' . $handler->request_array()[0]);
        if ($handler->request_array()[0] !== $virtual_route) {
            $virtualized_url = str_replace(
                get_base_url($handler->request_array()[0]),
                get_base_url($virtual_route),
                get_current_url()
            );
            redirect($virtualized_url, 301);

            return;
        }
    }
    if ($base !== 'index' && ! is_route_available($handler->request_array()[0])) {
        foreach ($virtual_routes as $k) {
            if ($handler->request_array()[0] === getSetting('route_' . $k)) {
                $handler->mapRoute($k);
            }
        }
    }
    if (getSetting('website_privacy_mode') === 'private' && ! Login::getUser()) {
        $allowed_requests = ['api', 'login', 'logout', 'page', 'account', 'connect', 'json', 'captcha-verify'];
        foreach ($virtual_routes as $v) {
            $v = getSetting('route_' . $v);
            if (isset($v)) {
                $allowed_requests[] = $v;
            }
        }
        if (getSetting('enable_signups')) {
            $allowed_requests[] = 'signup';
        }
        if (! in_array($handler->request_array()[0], $allowed_requests, true)) {
            headersNoCache();
            redirect('login', 302);
        }
    }
    $handler::setCond(
        'private_gate',
        getSetting('website_privacy_mode') === 'private'
            && ! Login::getUser()
    );
    $handler::setCond(
        'forced_private_mode',
        getSetting('website_privacy_mode') === 'private'
            && getSetting('website_content_privacy_mode') !== 'default'
    );
    $handler::setCond(
        'explore_enabled',
        $handler::cond('content_manager')
            ?: (getSetting('website_explore_page')
                ? ((bool) Login::isLoggedUser() ?: getSetting('website_explore_page_guest'))
                : false)
    );
    $handler::setCond(
        'search_enabled',
        $handler::cond('content_manager')
            ?: (
                getSetting('website_search')
                && (Login::isLoggedUser() ?: getSetting('website_search_guest'))
            )
    );
    $handler::setCond(
        'random_enabled',
        getSetting('website_random')
        && (Login::isLoggedUser() ?: getSetting('website_random_guest'))
    );
    $moderate_uploads = false;
    switch (getSetting('moderate_uploads')) {
        case 'all':
            $moderate_uploads = ! $handler::cond('content_manager');

            break;
        case 'guest':
            $moderate_uploads = ! Login::isLoggedUser();

            break;
    }
    $handler::setCond('moderate_uploads', $moderate_uploads);
    $categories = [];
    $tags_top = [];
    if ($handler::cond('explore_enabled') || $base === 'dashboard') {
        try {
            $categories_db = DB::queryFetchAll(
                'SELECT * FROM '
                    . DB::getTable('categories')
                    . ' ORDER BY category_name ASC;'
            );
            foreach ($categories_db as $k => $v) {
                $key = $v['category_id'];
                $categories[$key] = $v;
                $categories[$key]['category_url'] = get_base_url('category/' . $v['category_url_key']);
                $categories[$key] = DB::formatRow($categories[$key]);
            }
        } catch (Throwable) {
        }

        try {
            $tagsTable = DB::getTable('tags');
            $tags_db = DB::queryFetchAll(
                <<<MYSQL
                SELECT t.tag_name name, t.tag_id id, t.tag_files files, t.tag_views views
                FROM `{$tagsTable}` t
                ORDER BY `tag_files` DESC, `tag_name` ASC
                LIMIT 30;

                MYSQL
            );
            foreach ($tags_db as $k => $v) {
                $tag = array_merge($v, Tag::row($v['name']));
                $tags_top[] = $tag;
            }
        } catch (Throwable) {
        }
    }
    $handler::setVar('categories', $categories);
    $handler::setVar('tags_top', $tags_top);
    $explore_discovery = [
        'recent' => [
            'label' => _s('Recent'),
            'icon' => 'fas fa-history',
        ],
        'trending' => [
            'label' => _s('Trending'),
            'icon' => 'fas fa-chart-simple',
        ],
        'popular' => [
            'label' => _s('Popular'),
            'icon' => 'fas fa-heart',
        ],
    ];
    $explore_content = [
        'images' => [
            'label' => _n('Image', 'Images', 20),
            'icon' => 'fas fa-image',
        ],
        'videos' => [
            'label' => _n('Video', 'Videos', 20),
            'icon' => 'fas fa-video',
        ],
        'animated' => [
            'label' => _s('Animated'),
            'icon' => 'fas fa-play',
        ],
        'tags' => [
            'label' => _n('Tag', 'Tags', 20),
            'icon' => 'fas fa-tags',
        ],
        'albums' => [
            'label' => _n('Album', 'Albums', 20),
            'icon' => 'fas fa-photo-film',
        ],
        'users' => [
            'label' => _n('User', 'Users', 20),
            'icon' => 'fas fa-users',
        ],
    ];
    if (Login::isLoggedUser() && getSetting('enable_followers')) {
        $explore_discovery['following'] = [
            'label' => _s('Following'),
            'icon' => 'fas fa-rss',
            'url' => get_base_url('following'),
        ];
    }
    if (! getSetting('enable_likes')) {
        unset($explore_discovery['popular']);
    }
    foreach ($explore_discovery as $k => &$v) {
        $v['url'] = get_base_url('explore/' . $k);
    }
    foreach ($explore_content as $k => &$v) {
        $v['url'] = get_base_url('explore/' . $k);
    }
    unset($v);
    $handler::setVar('explore_discovery', $explore_discovery);
    $handler::setVar('explore_content', $explore_content);
    $versionInstalled = cheveretoVersionInstalled();
    $pages_visible = [];
    if (version_compare($versionInstalled, '3.6.7', '>=')) {
        $pages_visible_db = Page::getAll(
            args: [
                'is_active' => '1',
                'is_link_visible' => '1',
            ],
            sort: [
                'field' => 'sort_display',
                'order' => 'ASC',
            ]
        );
        $handler::setVar('page_tos', $pages_visible_db['tos'] ?? null);
        $handler::setVar('page_privacy', $pages_visible_db['privacy'] ?? null);
    }
    if ((bool) env()['CHEVERETO_ENABLE_PAGES']) {
        foreach ($pages_visible_db ?? [] as $k => $v) {
            if (! ($v['is_active'] ?? false) && ! ($v['is_link_visible'] ?? false)) {
                continue;
            }
            $pages_visible[$v['id']] = $v;
        }
    }
    $api_page = [
        'type' => 'link',
        'link_url' => get_base_url('api-v1'),
        'icon' => 'fas fa-project-diagram',
        'title' => 'API',
        'is_active' => 1,
        'is_link_visible' => 1,
        'attr_target' => '_self',
        'sort_display' => -2,
    ];
    Page::fill($api_page);
    $pages_visible[] = $api_page;
    if (getSetting('enable_plugin_route')) {
        $plugin_page = [
            'type' => 'link',
            'link_url' => get_base_url('plugin'),
            'icon' => 'fas fa-plug-circle-plus',
            'title' => _s('Plugin'),
            'is_active' => 1,
            'is_link_visible' => 1,
            'attr_target' => '_self',
            'sort_display' => -1,
        ];
        Page::fill($plugin_page);
        $pages_visible[] = $plugin_page;
    }
    uasort($pages_visible, function ($a, $b) {
        return $a['sort_display'] - $b['sort_display'];
    });
    $handler::setVar('pages_link_visible', $pages_visible);
    $upload_enabled = Login::isAdmin() ?: getSetting('enable_uploads');
    $upload_allowed = $upload_enabled;
    if (! Login::getUser()) {
        if (! getSetting('guest_uploads') || getSetting('website_privacy_mode') === 'private' || $handler::cond('maintenance')) {
            $upload_allowed = false;
        }
    } elseif (! Login::isAdmin() && getSetting('website_mode') === 'personal' && getSetting('website_mode_personal_uid') !== Login::getUser()['id']) {
        $upload_allowed = false;
    }
    if ((! (bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) && getVariable('storages_active')->nullInt() === 0) {
        $upload_enabled = false;
        $upload_allowed = false;
    }
    if (! Login::getUser() && $upload_allowed && getSetting('upload_max_filesize_mb_guest')) {
        Settings::setValue('upload_max_filesize_mb_bak', getSetting('upload_max_filesize_mb'));
        Settings::setValue('upload_max_filesize_mb', getSetting('upload_max_filesize_mb_guest'));
    }
    if ($upload_allowed
        && in_array($handler->request_array()[0], ['login', 'signup', 'account'], true)
    ) {
        $upload_allowed = false;
    }
    $handler::setCond('upload_enabled', $upload_enabled); // System allows to upload?
    $handler::setCond('upload_allowed', $upload_allowed); // Target peer can upload?
    if ($handler::cond('maintenance') || $handler::cond('show_consent_screen')) {
        $handler::setCond('private_gate', true);
        $allowed_requests = ['login', 'account', 'connect', 'captcha-verify', 'oembed'];
        if (! in_array($handler->request_array()[0], $allowed_requests, true)) {
            $handler->preventRoute($handler::cond('show_consent_screen') ? 'consent-screen' : 'maintenance');
        }
    }
    $handler::setVar('system_notices', Login::isAdmin() ? getSystemNotices() : []);
    $excludeLastUrl = [
        'login',
        'signup',
        'account',
        'connect',
        'logout',
        'json',
        'api',
        'captcha-verify',
        'webmanifest',
        'tag-autocomplete',
    ];
    if (! in_array($handler->request_array()[0], $excludeLastUrl, true)) {
        sessionVar()->put('last_url', get_current_url());
    }
    $detect = new Mobile_Detect();
    $isMobile = $detect->isMobile();
    $handler::setCond('mobile_device', (bool) $isMobile);
    $handler::setCond('show_viewer_zero', false);
    if ($handler->template() === 'request-denied') {
        $handler::setVar('doctitle', _s('Request denied') . ' (403) - ' . getSetting('website_name'));
        $handler->preventRoute('request-denied');
    }
    $handler::setVar('tos_privacy_agreement', _s('I agree to the %terms_link and %privacy_link', [
        '%terms_link' => '<a ' . ($handler::var('page_tos')['link_attr'] ?? '') . '>' . _s('terms') . '</a>',
        '%privacy_link' => '<a ' . ($handler::var('page_privacy')['link_attr'] ?? '') . '>' . _s('privacy policy') . '</a>',
    ]));
    $show_powered_by_footer = getSetting('enable_powered_by');
    if (array_key_exists('CHEVERETO_ENABLE_FORCE_POWERED_BY_FOOTER', env())) {
        if ((bool) env()['CHEVERETO_ENABLE_FORCE_POWERED_BY_FOOTER']) {
            $show_powered_by_footer = true;
        }
    }
    $handler::setCond('show_powered_by_footer', $show_powered_by_footer);
};
$hook_after = function (Handler $handler) {
    if (array_key_exists('deleted', get())
        && in_array($handler->template(), ['user', 'album'], true)
    ) {
        set_status_header(303);
    }
    if ($handler->template() === '404') {
        if (sessionVar()->hasKey('last_url')) {
            sessionVar()->remove('last_url');
        }
        $handler::setVar('doctitle', _s("That page doesn't exist") . ' (404) - ' . getSetting('website_name'));
    }
    $list_params = $handler::var('list_params');
    if (isset($list_params) && $list_params['page_show']) {
        $handler::setVar('doctitle', $handler::var('doctitle') . ' | ' . _s('Page %s', $list_params['page_show']));
    }
    if (defined('PUSH_LANG')) {
        $handler::setVar('doctitle', $handler::var('doctitle') . ' (' . get_enabled_languages()[PUSH_LANG]['name'] . ')');
    }
    $handler::setVar('safe_html_website_name', getSetting('website_name', true));
    $handler::setVar('safe_html_doctitle', safe_html($handler::var('doctitle')));
    if ($handler::var('pre_doctitle')) {
        $handler::setVar('safe_html_pre_doctitle', safe_html($handler::var('pre_doctitle')));
    }
    if ($handler::var('meta_description')) {
        $handler::setVar('safe_html_meta_description', safe_html($handler::var('meta_description')));
    }
    if ($handler::var('meta_keywords')) {
        $handler::setVar('safe_html_meta_keywords', safe_html($handler::var('meta_keywords')));
    }
    sessionVar()->put('REQUEST_REFERER', get_current_url());
    header('X-Powered-By: Chevereto 4');
};
// @phpstan-ignore-next-line
new Handler(loadTemplate: ! REPL, before: $hook_before, after: $hook_after);
