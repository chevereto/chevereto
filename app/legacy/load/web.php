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
use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\IpBan;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Page;
use Chevereto\Legacy\Classes\Palettes;
use Chevereto\Legacy\Classes\RequestLog;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\is_route_available;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\set_status_header;
use function Chevereto\Legacy\get_enabled_languages;
use function Chevereto\Legacy\getIdFromURLComponent;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSystemNotices;
use function Chevereto\Legacy\is_max_invalid_request;
use function Chevereto\Vars\cookie;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

if (Settings::get('chevereto_version_installed') === null) {
    new Handler(
        loadTemplate: !REPL, // @phpstan-ignore-line
        before: function ($handler) {
            if ($handler->request_array()[0] !== 'install') {
                redirect('install');
            }
        },
    );
}
$bannedIp = IpBan::getSingle();
if ($bannedIp !== []) {
    is_url($bannedIp['message'])
        ? redirect($bannedIp['message'])
        : (
            die(empty($bannedIp['message'])
                ? _s('You have been forbidden to use this website.')
                : $bannedIp['message']
            )
        );
}
$hook_before = function (Handler $handler) {
    header('Permissions-Policy: interest-cohort=()');
    header("Content-Security-Policy: frame-ancestors 'none'");
    $failed_access_requests = RequestLog::getCounts(['login', 'signup'], 'fail');
    if (is_max_invalid_request($failed_access_requests['day'])) {
        set_status_header(403);
    } else {
        Login::tryLogin();
    }
    $user_lang = cookie()['USER_SELECTED_LANG'] ?? getSetting('default_language');
    if (Login::isLoggedUser()) {
        $user_lang = Login::getUser()['language'];
        if (Login::getUser()['status'] === 'banned') {
            set_status_header(403);
        }
        if (sessionVar()->hasKey('challenge_two_factor')
            && !in_array($handler->getRoutePath(), ['account/two-factor', 'captcha-verify', 'logout'])
            && $handler->request_array()[0] !== 'page'
            ) {
            redirect('account/two-factor');
        }
    }
    if (!getSetting('language_chooser_enable')) {
        $user_lang = getSetting('default_language');
    }
    new L10n(
        $user_lang,
        Login::isLoggedUser()
            ? false
            : getSetting('auto_language')
    );
    if (http_response_code() == 403) {
        die();
    }
    if ($handler->request_array()[0] !== 'api'
        && Settings::get('enable_uploads_url') && !Login::isAdmin()) {
        Settings::setValue('enable_uploads_url', 0);
    }
    if (isset(get()['lang']) && array_key_exists(get()['lang'], get_enabled_languages())) {
        if (Login::isLoggedUser() && Login::getUser()['language'] !== get()['lang']) {
            User::update(Login::getUser()['id'], ['language' => get()['lang']]);
        }
        L10n::setCookieLang(get()['lang']);
        L10n::processTranslation(get()['lang']);
        define('PUSH_LANG', get()['lang']);
    }
    if (array_key_exists('agree-consent', get())) {
        setcookie('AGREE_CONSENT', "1", time() + (60 * 60 * 24 * 30), Config::host()->hostnamePath());
        sessionVar()->put('agree-consent', true);
        redirect(get_current_url(true, ['agree-consent']));
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
    $allowed_nsfw_flagging = !getSetting('image_lock_nsfw_editing');
    if ($handler::cond('content_manager')) {
        $moderateLink = get_base_url('moderate');
        $moderateLabel = _s('Moderate');
        if (!(bool) env()['CHEVERETO_ENABLE_MODERATION']) {
            if ((bool) env()['CHEVERETO_ENABLE_EXPOSE_PAID_FEATURES']) {
                $moderateLink = 'https://chevereto.com/pricing';
                $moderateLabel .= ' ' . badgePaid();
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
    $handler::setCond('maintenance', getSetting('maintenance') and !Login::isAdmin());
    $handler::setCond(
        'show_consent_screen',
        $base !== 'api' && (
            getSetting('enable_consent_screen')
            ? !(Login::getUser() || isset(session()['agree-consent']) || isset(cookie()['AGREE_CONSENT']))
            : false
        )
    );
    $handler::setCond('captcha_needed', getSetting('captcha') && getSetting('captcha_threshold') == 0);
    $handler::setCond('show_header', !($handler::cond('maintenance') || $handler::cond('show_consent_screen')));
    $handler::setCond('show_notifications', getSetting('website_mode') == 'community' && (getSetting('enable_followers') || getSetting('enable_likes')));
    $handler::setCond('allowed_to_delete_content', Login::isAdmin() || getSetting('enable_user_content_delete'));
    $handler::setVar('canonical', null);
    $palettes = new Palettes();
    $handler::setVar('palettes', $palettes);
    if (in_array($handler->request_array()[0], ['login', 'signup', 'account'])) {
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
    if ($handler::cond('maintenance') && $handler->request_array()[0] == 'dashboard') {
        redirect('login');
    }
    $langLinks = [];
    $langToggleUrl = get_current_url(true, ['lang']);
    parse_str(server()['QUERY_STRING'] ?? '', $qs);
    unset($qs['lang']);
    $qs = http_build_query($qs);
    $langToggleUrl = rtrim($langToggleUrl, '/') . ($qs ? '&' : '/?') . 'lang=';
    foreach (get_enabled_languages() as $k => $v) {
        $hreflang = strtolower($k);
        $langUrl = $langToggleUrl . $k;
        $langLinks[$k] = [
            'hreflang' => $hreflang,
            'name' => $v['name'],
            'url' => $langUrl,
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
    if (!Login::getUser()) {
        if (getSetting('captcha') && $failed_access_requests['day'] >= getSetting('captcha_threshold')) {
            $handler::setCond('captcha_needed', true);
        }
    }
    if (getSetting('website_mode') == 'personal') {
        if ($handler->request_array()[0] == '/'
            && getSetting('website_mode_personal_routing') == '/'
            && in_array(key($querystr), ['random'])
        ) {
            $handler->mapRoute('index');
        } elseif ($handler->request_array()[0] == 'search'
            && in_array($handler->request_array()[1] ?? [], ['images', 'albums', 'users'])
        ) {
            $handler->mapRoute('search');
        } elseif ($handler->request_array()[0] == getSetting('website_mode_personal_routing')
            || (getSetting('website_mode_personal_routing') == '/'
            && in_array($handler->request_array()[0], ['albums', 'search']))
        ) {
            $handler->mapRoute('user', [
                'id' => getSetting('website_mode_personal_uid'),
            ]);
        }
        if ($handler->request_array()[0] == '/'
            && !in_array(key($querystr), ['random', 'lang'])
            && !$handler::cond('mapped_route')
        ) {
            $personal_mode_user = User::getSingle(getSetting('website_mode_personal_uid'));
            if ($personal_mode_user !== []) {
                if (Settings::get('homepage_cta_html') == null) {
                    Settings::setValue('homepage_cta_html', _s('View all my images'));
                }
                if (Settings::get('homepage_title_html') == null) {
                    Settings::setValue('homepage_title_html', $personal_mode_user['name']);
                }
                if (Settings::get('homepage_paragraph_html') == null) {
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
        if ($base !== 'index' and !is_route_available($handler->request_array()[0])) {
            $mapTo = getSetting('root_route');
            $handler->mapRoute($mapTo);
        }
    }
    $virtualizable_routes = ['image', 'album', 'user'];
    if (in_array($handler->request_array()[0], $virtualizable_routes)) {
        $virtual_route = getSetting('route_' . $handler->request_array()[0]);
        if ($handler->request_array()[0] !== $virtual_route) {
            $virtualized_url = str_replace(
                get_base_url($handler->request_array()[0]),
                get_base_url($virtual_route),
                get_current_url()
            );
            redirect($virtualized_url);

            return;
        }
    }
    if ($base !== 'index' && !is_route_available($handler->request_array()[0])) {
        foreach ($virtualizable_routes as $k) {
            if ($handler->request_array()[0] == getSetting('route_' . $k)) {
                $handler->mapRoute($k);
            }
        }
    }
    if (getSetting('website_privacy_mode') == 'private' && !Login::getUser()) {
        $allowed_requests = ['api', 'login', 'logout', 'page', 'account', 'connect', 'json', 'captcha-verify'];
        foreach ($virtualizable_routes as $v) {
            $v = getSetting('route_' . $v);
            if (isset($v)) {
                $allowed_requests[] = $v;
            }
        }
        if (getSetting('enable_signups')) {
            $allowed_requests[] = 'signup';
        }
        if (!in_array($handler->request_array()[0], $allowed_requests)) {
            redirect('login');
        }
    }
    $handler::setCond('private_gate', getSetting('website_privacy_mode') == 'private' and !Login::getUser());
    $handler::setCond('forced_private_mode', (getSetting('website_privacy_mode') == 'private' and getSetting('website_content_privacy_mode') !== 'default'));
    $handler::setCond('explore_enabled', $handler::cond('content_manager') ?: (getSetting('website_explore_page') ? ((bool) Login::isLoggedUser() ?: getSetting('website_explore_page_guest')) : false));
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
        $handler::cond('content_manager')
            ?: (
                getSetting('website_random')
                && (Login::isLoggedUser() ?: getSetting('website_random_guest'))
            )
    );
    $moderate_uploads = false;
    switch (getSetting('moderate_uploads')) {
        case 'all':
            $moderate_uploads = !$handler::cond('content_manager');

        break;
        case 'guest':
            $moderate_uploads = !Login::isLoggedUser();

        break;
    }
    $handler::setCond('moderate_uploads', $moderate_uploads);
    $categories = [];
    if ($handler::cond('explore_enabled') || $base == 'dashboard') {
        try {
            $categories_db = DB::queryFetchAll('SELECT * FROM ' . DB::getTable('categories') . ' ORDER BY category_name ASC;');
            if (count($categories_db) > 0) {
                foreach ($categories_db as $k => $v) {
                    $key = $v['category_id'];
                    $categories[$key] = $v;
                    $categories[$key]['category_url'] = get_base_url('category/' . $v['category_url_key']);
                    $categories[$key] = DB::formatRow($categories[$key]);
                }
            }
        } catch (Throwable $e) {
        }
    }
    if ($handler::cond('explore_enabled')
        && $categories === []
        && !(bool) env()['CHEVERETO_ENABLE_USERS']
    ) {
        $handler::setCond('explore_enabled', false);
    }
    $handler::setVar('categories', $categories);
    $explore_semantics = [
        'recent' => [
            'label' => _s('Recent'),
            'icon' => 'fas fa-history',
        ],
        'trending' => [
            'label' => _s('Trending'),
            'icon' => 'fas fa-poll',
        ],
        'popular' => [
            'label' => _s('Popular'),
            'icon' => 'fas fa-heart',
        ],
        'animated' => [
            'label' => _s('Animated'),
            'icon' => 'fas fa-play',
        ],
    ];
    if (!(bool) env()['CHEVERETO_ENABLE_USERS']) {
        $explore_semantics = [];
    }
    if (!getSetting('enable_likes')) {
        unset($explore_semantics['popular']);
    }
    if (!in_array('gif', Image::getEnabledImageFormats())) {
        unset($explore_semantics['animated']);
    }
    foreach ($explore_semantics as $k => &$v) {
        $v['url'] = get_base_url('explore/' . $k);
    }
    unset($v);
    $handler::setVar('explore_semantics', $explore_semantics);
    if (version_compare(Settings::get('chevereto_version_installed'), '3.6.7', '>=')) {
        $pages_visible_db = Page::getAll(['is_active' => 1, 'is_link_visible' => 1], ['field' => 'sort_display', 'order' => 'ASC']);
        $pageHandle = version_compare(Settings::get('chevereto_version_installed'), '3.12.4', '>=') ? 'internal' : 'url_key';
        $handler::setVar('page_tos', Page::getSingle('tos', $pageHandle));
        $handler::setVar('page_privacy', Page::getSingle('privacy', $pageHandle));
    }
    $pages_visible = [];
    if ((bool) env()['CHEVERETO_ENABLE_PAGES']) {
        foreach ($pages_visible_db as $k => $v) {
            if (!($v['is_active'] ?? false) && !($v['is_link_visible'] ?? false)) {
                continue;
            }
            $pages_visible[$v['id']] = $v;
        }
        if (getSetting('enable_plugin_route')) {
            $plugin_page = [
                'type' => 'link',
                'link_url' => get_base_url('plugin'),
                'icon' => 'fas fa-code',
                'title' => _s('Plugin'),
                'is_active' => 1,
                'is_link_visible' => 1,
                'attr_target' => '_self',
                'sort_display' => 999,
            ];
            Page::fill($plugin_page);
            $pages_visible[] = $plugin_page;
        }
    }
    $handler::setVar('pages_link_visible', $pages_visible);
    $upload_enabled = Login::isAdmin() ?: getSetting('enable_uploads');
    $upload_allowed = $upload_enabled;
    if (!Login::getUser()) {
        if (!getSetting('guest_uploads') || getSetting('website_privacy_mode') == 'private' || $handler::cond('maintenance')) {
            $upload_allowed = false;
        }
    } elseif (!Login::isAdmin() && getSetting('website_mode') == 'personal' && getSetting('website_mode_personal_uid') !== Login::getUser()['id']) {
        $upload_allowed = false;
    }
    if (!Login::getUser() && $upload_allowed && getSetting('upload_max_filesize_mb_guest')) {
        Settings::setValue('upload_max_filesize_mb_bak', getSetting('upload_max_filesize_mb'));
        Settings::setValue('upload_max_filesize_mb', getSetting('upload_max_filesize_mb_guest'));
    }
    if ($upload_allowed && in_array($handler->request_array()[0], ['login', 'signup', 'account'])) {
        $upload_allowed = false;
    }
    $handler::setCond('upload_enabled', $upload_enabled); // System allows to upload?
    $handler::setCond('upload_allowed', $upload_allowed); // Target peer can upload?
    if ($handler::cond('maintenance') || $handler::cond('show_consent_screen')) {
        $handler::setCond('private_gate', true);
        $allowed_requests = ['login', 'account', 'connect', 'captcha-verify', 'oembed'];
        if (!in_array($handler->request_array()[0], $allowed_requests)) {
            $handler->preventRoute($handler::cond('show_consent_screen') ? 'consent-screen' : 'maintenance');
        }
    }
    if ($handler->request_array()[0] == getSetting('route_image')) {
        $id = getIdFromURLComponent($handler->request()[0] ?? '');
        if ($id !== 0) {
            $image = Image::getSingle($id, false, true, $handler::var('logged_user'));
            $userNotBanned = ($image['user']['status'] ?? '') != 'banned';
            if ($image !== [] && $image['is_approved'] && $userNotBanned && !in_array($image['album']['privacy'] ?? '', ['private', 'custom'])) {
                $image_safe_html = safe_html($image);
                $handler::setVar('oembed', [
                    'title' => ($image_safe_html['title'] ?? ($image_safe_html['name'] . '.' . $image['extension'])) . ' hosted at ' . getSetting('website_name'),
                    'url' => $image['url_viewer']
                ]);
            }
        }
    }
    $handler::setVar('system_notices', Login::isAdmin() ? getSystemNotices() : []);
    if (!in_array($handler->request_array()[0], ['login', 'signup', 'account', 'connect', 'logout', 'json', 'api', 'captcha-verify'])) {
        sessionVar()->put('last_url', get_current_url());
    }
    $detect = new Mobile_Detect();
    $isMobile = $detect->isMobile();
    $handler::setCond('mobile_device', (bool) $isMobile);
    $handler::setCond('show_viewer_zero', false);
    if ($handler->template() == 'request-denied') {
        $handler::setVar('doctitle', _s("Request denied") . ' (403) - ' . getSetting('website_name'));
        $handler->preventRoute('request-denied');
    }
    $handler::setVar('tos_privacy_agreement', _s('I agree to the %terms_link and %privacy_link', [
        '%terms_link' => '<a ' . ($handler::var('page_tos')['link_attr'] ?? '') . '>' . _s('terms') . '</a>',
        '%privacy_link' => '<a ' . ($handler::var('page_privacy')['link_attr'] ?? '') . '>' . _s('privacy policy') . '</a>'
    ]));
    $poweredBySiteWide = (bool) env()['CHEVERETO_ENABLE_POWERED_BY_FOOTER_SITE_WIDE'];
    $show_powered_by_footer = $poweredBySiteWide
        ?: ($handler->getRoutePath() === 'index' && getSetting('enable_powered_by'));
    if (in_array($handler->getRoutePath(false), ['settings', 'dashboard'])) {
        $show_powered_by_footer = false;
    }
    $handler::setCond('show_powered_by_footer', $show_powered_by_footer);
};
$hook_after = function (Handler $handler) {
    if (array_key_exists('deleted', get()) && in_array($handler->template(), ['user', 'album'])) {
        set_status_header(303);
    }
    if ($handler->template() == '404') {
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
    $handler::setVar('safe_html_website_name', safe_html(getSetting('website_name')));
    $handler::setVar('safe_html_doctitle', safe_html($handler::var('doctitle')));
    if ($handler::var('pre_doctitle')) {
        $handler::setVar('safe_html_pre_doctitle', safe_html($handler::var('pre_doctitle')));
    }
    $handler::setVar('safe_html_meta_description', safe_html($handler::var('meta_description')));
    sessionVar()->put('REQUEST_REFERER', get_current_url());
    header('X-Powered-By: Chevereto 4');
};
// @phpstan-ignore-next-line
new Handler(loadTemplate: !REPL, before: $hook_before, after: $hook_after, );
