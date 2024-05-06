<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\String\randomString;
use Chevereto\Config\Config;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\Akismet;
use Chevereto\Legacy\Classes\Arachnid;
use Chevereto\Legacy\Classes\AssetStorage;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Mailer;
use Chevereto\Legacy\Classes\Page;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\Stat;
use Chevereto\Legacy\Classes\Upload;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\editionCombo;
use function Chevereto\Legacy\G\abbreviate_number;
use function Chevereto\Legacy\G\absolute_to_relative;
use function Chevereto\Legacy\G\absolute_to_url;
use function Chevereto\Legacy\G\check_value;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\fetch_url;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\get_app_version;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_ffmpeg_error;
use function Chevereto\Legacy\G\get_ini_bytes;
use function Chevereto\Legacy\G\get_regex_match;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\is_integer;
use function Chevereto\Legacy\G\is_route_available;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\is_url_web;
use function Chevereto\Legacy\G\is_valid_url;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\sanitize_relative_path;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\get_available_languages;
use function Chevereto\Legacy\get_chv_default_setting;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getLicenseKey;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Legacy\getSystemNotices;
use function Chevereto\Legacy\updateCheveretoNews;
use function Chevereto\Legacy\upload_to_content_images;
use function Chevereto\Vars\env;
use function Chevereto\Vars\files;
use function Chevereto\Vars\get;
use function Chevereto\Vars\post;
use function Chevereto\Vars\request;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Intervention\Image\ImageManagerStatic;
use PHPMailer\PHPMailer\SMTP;

return function (Handler $handler) {
    $POST = post();
    if ($POST !== [] && !$handler::checkAuthToken(request()['auth_token'] ?? '')) {
        $handler->issueError(403);

        return;
    }
    if (env()['CHEVERETO_CONTEXT'] !== 'saas'
        && ($handler->request()[0] ?? null) === 'upgrade'
    ) {
        if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
            $handler->issueError(403);

            return;
        }
        $upgradingDir = PATH_APP . '.upgrading/';
        if (!is_dir($upgradingDir)) {
            mkdir($upgradingDir);
        }
        $upgradingLock = $upgradingDir . 'upgrading.lock';
        unlinkIfExists($upgradingLock);
        $token = randomString(128);
        touch($upgradingLock);
        file_put_contents($upgradingLock, $token);
        $params = [
            'action' => 'download',
            'token' => $token,
            'return' => 'dashboard/?installed',
        ];
        $query = http_build_query($params);
        redirect(
            get_base_url('upgrading/?' . $query),
            302,
        );
    }
    $doing = $handler->request()[0] ?? 'stats';
    $logged_user = Login::getUser();
    if ($logged_user === []) {
        redirect('login');
    }
    if ($doing == 'user' && $handler::cond('content_manager')) {
        $route = $handler->getRouteFn('settings');
        $handler::setCond('dashboard_user', true);

        return $route($handler);
    }
    if (!$logged_user['is_admin']) {
        $handler->issueError(404);

        return;
    }
    $route_prefix = 'dashboard';
    $routes = [
        'stats' => _s('Home'),
        'images' => _n('File', 'Files', 20),
        'albums' => _n('Album', 'Albums', 20),
        'users' => _n('User', 'Users', 20),
        'bulk-importer' => _s('Bulk importer'),
        'settings' => _s('Settings'),
        'run-cron' => _s('Run %s', 'CRON'),
    ];
    $routesLinkLabels = $routes;
    $paidRoutes = [];
    $paidRoutesEnv = [
        'albums' => ['lite', 'CHEVERETO_ENABLE_USERS'],
        'bulk-importer' => ['pro', 'CHEVERETO_ENABLE_BULK_IMPORTER'],
        'images' => ['lite', 'CHEVERETO_ENABLE_USERS'],
        'users' => ['lite', 'CHEVERETO_ENABLE_USERS'],
    ];
    foreach ($paidRoutesEnv as $k => $v) {
        $isEnabled = in_array($v[0], editionCombo()[env()['CHEVERETO_EDITION']]);
        if (!(bool) env()['CHEVERETO_ENABLE_EXPOSE_PAID_FEATURES'] && !$isEnabled) {
            unset($routes[$k]);

            continue;
        }
        if (!$isEnabled) {
            array_push($paidRoutes, $k);
            $routes[$k] .= ' ' . badgePaid($v[0]);
        }
    }
    $icons = [
        'albums' => 'fas fa-images',
        'bulk-importer' => 'fas fa-layer-group',
        'images' => 'fas fa-photo-film',
        'run-cron' => 'fas fa-bolt',
        'settings' => 'fas fa-cog',
        'stats' => 'fas fa-home',
        'users' => 'fas fa-users',
    ];
    $settings_sections = [
        'website' => _s('Website'),
        'content' => _s('Content'),
        'listings' => _s('Listings'),
        'file-uploads' => _s('File uploads'),
        'semantics' => _s('Semantics'),
        'categories' => _s('Categories'),
        'theme' => _s('Theme'),
        'system' => _s('System'),
        'languages' => _s('Languages'),
        'email' => _s('Email'),
        'tools' => _s('Tools'),
        'homepage' => _s('Homepage'),
        'pages' => _s('Pages'),
        'upload-plugin' => _s('Upload plugin'),
        'consent-screen' => _s('Consent screen'),
        'users' => _n('User', 'Users', 20),
        'guest-api' => _s('Guests %s', 'API'),
        'logo' => _s('Logo'),
        'external-storage' => _s('External storage'),
        'routing' => _s('Routing'),
        'external-services' => _s('External services'),
        'login-providers' => _s('Login providers'),
        'cookie-compliance' => _s('Cookie compliance'),
        'flood-protection' => _s('Flood protection'),
        'banners' => _s('Banners'),
        'ip-bans' => _s('IP bans'),
        'watermarks' => _s('Watermarks'),
    ];
    $settings_sections_icons = [
        'banners' => 'fas fa-scroll',
        'categories' => 'fas fa-columns',
        'consent-screen' => 'fas fa-desktop',
        'content' => 'fas fa-cubes',
        'cookie-compliance' => 'fas fa-cookie-bite',
        'email' => 'fas fa-at',
        'external-services' => 'fas fa-concierge-bell',
        'external-storage' => 'fas fa-hdd',
        'file-uploads' => 'fas fa-cloud-upload-alt',
        'flood-protection' => 'fas fa-faucet',
        'guest-api' => 'fas fa-project-diagram',
        'homepage' => 'fas fa-home',
        'ip-bans' => 'fas fa-ban',
        'languages' => 'fas fa-language',
        'listings' => 'fas fa-th-list',
        'login-providers' => 'fas fa-right-to-bracket',
        'logo' => 'fas fa-gem',
        'pages' => 'fas fa-file',
        'routing' => 'fas fa-route',
        'semantics' => 'fas fa-sign-hanging',
        'system' => 'fas fa-server',
        'theme' => 'fas fa-paint-brush',
        'tools' => 'fas fa-tools',
        'upload-plugin' => 'fas fa-plug',
        'users' => 'fas fa-users-cog',
        'watermarks' => 'fas fa-tint',
        'website' => 'fas fa-globe',
    ];
    $paidSettingsEnv = [
        'banners' => ['pro', 'CHEVERETO_ENABLE_BANNERS'],
        'consent-screen' => ['lite', 'CHEVERETO_ENABLE_CONSENT_SCREEN'],
        'cookie-compliance' => ['pro', 'CHEVERETO_ENABLE_COOKIE_COMPLIANCE'],
        'external-services' => ['pro', 'CHEVERETO_ENABLE_EXTERNAL_SERVICES'],
        'external-storage' => ['pro', 'CHEVERETO_ENABLE_EXTERNAL_STORAGE'],
        'flood-protection' => ['pro', 'CHEVERETO_ENABLE_UPLOAD_FLOOD_PROTECTION'],
        'guest-api' => ['lite', 'CHEVERETO_ENABLE_API_GUEST'],
        'homepage' => ['lite', 'CHEVERETO_ENABLE_USERS'],
        'ip-bans' => ['pro', 'CHEVERETO_ENABLE_IP_BANS'],
        'login-providers' => ['pro', 'CHEVERETO_ENABLE_LOGIN_PROVIDERS'],
        'logo' => ['pro', 'CHEVERETO_ENABLE_LOGO'],
        'pages' => ['lite', 'CHEVERETO_ENABLE_PAGES'],
        'routing' => ['pro', 'CHEVERETO_ENABLE_ROUTING'],
        'upload-plugin' => ['lite', 'CHEVERETO_ENABLE_UPLOAD_PLUGIN'],
        'users' => ['lite', 'CHEVERETO_ENABLE_USERS'],
        'watermarks' => ['pro', 'CHEVERETO_ENABLE_UPLOAD_WATERMARK'],
    ];
    $paidSettings = [];
    $default_route = 'stats';
    if (!is_null($doing) && !array_key_exists($doing, $routes)) {
        $handler->issueError(404);

        return;
    }
    if ($doing == '') {
        $doing = $default_route;
    }
    $route_menu = [];
    foreach ($routes as $route => $label) {
        $aux = str_replace('_', '-', $route);
        $handler::setCond($route_prefix . '_' . $aux, $doing == $aux);
        if ($handler::cond($route_prefix . '_' . $aux)) {
            $handler::setVar($route_prefix, $aux);
        }
        $route_menu[$route] = [
            'icon' => $icons[$route],
            'label' => $label,
            'url' => in_array($route, $paidRoutes)
                ? 'https://chevereto.com/pricing'
                : get_base_url($route_prefix . ($route == $default_route ? '' : '/' . $route)),
            'current' => $handler::cond($route_prefix . '_' . $aux)
        ];
    }
    $handler::setVar('docsBaseUrl', 'https://v4-docs.chevereto.com/');
    $handler::setVar('adminDocsBaseUrl', 'https://v4-admin.chevereto.com/');
    $handler::setVar('userDocsBaseUrl', 'https://v4-user.chevereto.com/');
    unset($route_menu['run-cron'], $routes['run-cron'], $routesLinkLabels['run-cron']);
    $handler::setVar($route_prefix . '_menu', $route_menu);
    $handler::setVar('tabs', $route_menu);
    $is_error = false;
    $is_changed = false;
    $input_errors = [];
    $error_message = null;
    if ($doing == '') {
        $doing = 'stats';
    }
    if (in_array($doing, $paidRoutes)) {
        $handler->issueError(404);
    }
    switch ($doing) {
        case 'run-cron':

            if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
                $handler->issueError(403);

                return;
            }
            ini_set('log_errors', true);
            ini_set('display_errors', true);
            ignore_user_abort(true);
            @set_time_limit(0);
            ini_set('default_charset', 'utf-8');
            setlocale(LC_ALL, 'en_US.UTF8');
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', false);
            echo '<pre>'
                . 'Trigger cron tasks (HTTP API)'
                . PHP_EOL
                . '--'
                . PHP_EOL;
            require_once PATH_APP_LEGACY . 'commands/cron.php';
            echo '</pre>';
            die(0);

        break;
        case 'stats':
            if (version_compare(getSetting('chevereto_version_installed'), '3.7.0', '<')) {
                $totals = [];
            } else {
                $totals = Stat::getTotals();
                if ($totals === []) {
                    $totals = [
                        'date_gmt' => null,
                        'users' => 0,
                        'images' => 0,
                        'albums' => 0,
                        'image_views' => 0,
                        'album_views' => 0,
                        'image_likes' => 0,
                        'album_likes' => 0,
                        'disk_used' => 0,
                    ];
                }
            }
            $totals_display = [];
            $total_handle = ['images', 'users', 'albums'];
            foreach ($total_handle as $v) {
                $totals_display[$v] = abbreviate_number($totals[$v]);
            }
            $format_disk_usage = explode(' ', format_bytes($totals['disk_used']));
            $totals_display['disk'] = ['used' => $format_disk_usage[0], 'unit' => $format_disk_usage[1]];
            if (empty($totals_display['disk']['used'])) {
                $totals_display['disk'] = [
                    'used' => 0,
                    'unit' => 'KB'
                ];
            }
            $db = DB::getInstance();
            $chevereto_news = unserialize(Settings::get('chevereto_news'));
            if (!is_array($chevereto_news) || $chevereto_news === []) {
                $chevereto_news = updateCheveretoNews();
            }
            $handler::setVar('chevereto_news', $chevereto_news);
            $chv_version = [
                'files' => get_app_version(),
                'db' => getSetting('chevereto_version_installed') ?? ''
            ];
            $links = [];
            $linksButtons = '';
            $licenseKey = getLicenseKey();
            $handler::setVar('licenseKey', $licenseKey);
            if (env()['CHEVERETO_CONTEXT'] !== 'saas') {
                $upgradeClass = 'hidden';
                $upgradeLink = get_base_url('dashboard/upgrade/?auth_token=' . $handler::getAuthToken());
                if ($licenseKey !== '' && env()['CHEVERETO_EDITION'] === 'free') {
                    $upgradeClass = '';
                }
                $upgradeTitle = '<i class=\"fa-solid fa-boxes-packing\"></i> ' . _s('Upgrade now');
                $links = array_merge($links, [
                    [
                        'label' => _s('Upgrade now'),
                        'icon' => 'fas fa-download',
                        'class' => 'green ' . $upgradeClass,
                        'attr' => 'data-action="upgrade" data-options=\'{"title":"' . $upgradeTitle . '"}\' href="' . $upgradeLink . '" data-confirm="' . _s("The latest release will be downloaded and extracted in the filesystem.") . '"',
                    ],
                ]);
                $links = array_merge($links, [
                    [
                        'label' => _s("License key"),
                        'icon' => 'fas fa-key',
                        'class' => 'accent outline',
                        'attr' => 'data-action="license" data-modal="edit" data-target="modal-license-key"'
                    ],
                ]);
            }
            if (env()['CHEVERETO_CONTEXT'] === 'saas') {
                $links = array_merge($links, [
                    [
                        'label' => _s('Support'),
                        'icon' => 'fas fa-medkit',
                        'href' => 'https://chevereto.cloud/support'
                    ],
                ]);
            }
            foreach ($links as $link) {
                $attr = $link['attr'] ?? 'href="%href%" target="_blank"';
                $class = $link['class'] ?? 'default';
                $linksButtons .= strtr('<a ' . $attr . ' class="btn btn-small ' . $class . ' margin-right-5"><span class="btn-icon fa-btn-icon %icon%"></span><span class="btn-text">%label%</span></a>', [
                    '%href%' => $link['href'] ?? '',
                    '%icon%' => $link['icon'],
                    '%label%' => $link['label'],
                ]);
            }
            $install_update_button = '';
            $version_check = '';
            $cronRemark = '<a href="'
            . get_base_url('dashboard/run-cron')
            . '?auth_token=' . $handler::getAuthToken()
            . '" target="_blank"><i class="icon fas fa-bolt margin-left-5 margin-right-5"></i>'
                . _s('Run %s', 'CRON')
                . '</a> ';
            $errorLogRemark = '';
            $cron_last_ran = Settings::get('cron_last_ran');
            if (env()['CHEVERETO_CONTEXT'] !== 'saas') {
                if (version_compare($chv_version['files'], $chv_version['db'], '>')) {
                    $install_update_button = $chv_version['db'] . ' DB <span class="fas fa-database"></span> <a href="' . get_base_url('update') . '">' . _s('install update') . '</a>';
                }
                $version_check .= '<a data-action="check-for-updates" class="btn btn-small accent margin-right-5 margin-bottom-5"><span class="fas fa-circle-up"></span> ' . _s("Check upgrades") . '</a>';
                if (datetime_diff($cron_last_ran, null, 'm') > 5) {
                    $cronRemark .= ' — <span class="color-fail"><span class="fas fa-exclamation-triangle"></span> ' . _s('not running') . '</span>';
                }
                if ((env()['CHEVERETO_SERVICING'] ?? null) === 'docker') {
                    $cronRemark .= '<div><code class="code code--command  display-inline-block" data-click="select-all" style="white-space: pre-wrap;">docker exec -it --user www-data ' . (gethostname() ?: 'chv-container') . ' app/bin/legacy -C cron</code></div>';
                    $errorLogRemark .= '<div><code class="code code--command  display-inline-block" data-click="select-all" style="white-space: pre-wrap;">docker logs ' . (gethostname() ?: 'chv-container') . ' -f 1>/dev/null</code></div>';
                }
            }
            $ffmpegContent = '<i class="fas fa-video"></i> ';

            try {
                $missing = [
                    'proc_open' => !function_exists('proc_open'),
                    'proc_close' => !function_exists('proc_close'),
                ];
                $missing = array_filter($missing);
                if ($missing) {
                    throw new Exception(
                        _s(
                            'PHP function [%s] not available in this PHP installation',
                            implode(', ', array_keys($missing))
                        )
                    );
                }
                $ffmpegErrors = [];

                try {
                    $ffmpeg = FFMpeg::create(
                        [
                            'ffmpeg.binaries' => env()['CHEVERETO_BINARY_FFMPEG'],
                            'ffprobe.binaries' => env()['CHEVERETO_BINARY_FFPROBE'],
                        ]
                    );
                } catch (Throwable $e) {
                    $ffmpegErrors[] = get_ffmpeg_error($e);
                }

                try {
                    $ffprobe = FFProbe::create(
                        [
                            'ffprobe.binaries' => env()['CHEVERETO_BINARY_FFPROBE'],
                        ]
                    );
                } catch (Throwable $e) {
                    $ffmpegErrors[] = get_ffmpeg_error($e);
                }
                if ($ffmpegErrors !== []) {
                    throw new Exception(implode(', ', $ffmpegErrors));
                }

                $ffprobe->getFFProbeDriver()->getName();
                $ffmpegContent .= 'FFmpeg bin:'
                    . env()['CHEVERETO_BINARY_FFMPEG']
                    . ' version '
                    . $ffmpeg->getFFMpegDriver()->getVersion()
                    . '<br>'
                    . '<i class="fas fa-circle-check"></i> FFprobe bin:'
                    . env()['CHEVERETO_BINARY_FFPROBE'];
            } catch (Throwable $e) {
                $ffmpegContent = '<span class="color-fail"><i class="fas fa-warning"></i> Error: '
                    . get_ffmpeg_error($e)
                    . '</span>';
            }

            $chv_versioning = explode('.', APP_VERSION);
            $chv_version_major = $chv_versioning[0] . '.X';
            $chv_version_minor = $chv_versioning[0] . '.' . $chv_versioning[1];
            $system_values = [
                'chv_version' => [
                    'label' => '<div class="text-align-center"><a href="https://chevereto.com" target="_blank"><img src="' . absolute_to_url(PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'chevereto-blue.svg') . '" alt="" width="80%"></a></div>',
                    'content' => '<div class="phone-text-align-center">'
                        . '<h3 class="margin-bottom-10"><a target="_blank" href="https://releases.chevereto.com/' . $chv_version_major . '/' . $chv_version_minor . '/' . $chv_version['files'] . '">'
                        . $chv_version['files']
                        . '<span class="btn-icon fas fa-code-branch margin-left-5"></span></a><span data-action="welcome" data-modal="simple" data-target="modal-welcome" class="software-version-name margin-left-10 cursor-pointer" title="' . APP_VERSION_AKA . '">' . APP_VERSION_AKA . '</span> </h3>'
                        . $install_update_button
                        . '<div class="margin-bottom-20">' . $version_check . $linksButtons . '</div>
                        </div>'
                ],
                'max_upload_size' => [
                    'label' => _s('Max. upload file size'),
                    'content' => '<i class="fas fa-upload"></i> ' . format_bytes(get_ini_bytes(ini_get('upload_max_filesize')))
                ],
                'graphics' => [
                    'label' => _s('Graphics library'),
                ],
                'video' => [
                    'label' => 'FFmpeg',
                    'content' => $ffmpegContent
                ],
                'rebuild_stats' => [
                    'label' => _s('Stats'),
                    'content' => '<a data-action="dashboardTool" data-tool="rebuildStats"><span class="fas fa-sync-alt margin-right-5"></span>' . _s('Rebuild stats') . '</a>'
                ],
                'connecting_ip' => [
                    'label' => _s('Connecting IP'),
                    'content' => '<i class="fas fa-network-wired"></i> ' . get_client_ip() . ' <a data-modal="simple" data-target="modal-connecting-ip"><i class="fas fa-question-circle margin-right-5"></i>' . _s('Not your IP?') . '</a>'
                ],
                'is_encrypted' => [
                    'label' => _s('Encryption'),
                    'content' => '<i class="fas fa-shield-halved"></i> ' . (hasEncryption() ? _s('Enabled') : _s('Disabled'))
                ],
                'meta' => [
                    'label' => _s('Meta'),
                    'content' => '<a class="btn default btn-small margin-right-5 margin-bottom-5" href="https://rodolfoberrios.com" target="_blank" rel="author"><span class="fas fa-address-card margin-right-5"></span>Rodolfo Berrios</a><a class="btn default btn-small margin-right-5 margin-bottom-5" href="https://chevere.org" target="_blank"><span class="fas fa-sitemap margin-right-5"></span>Chevere</a><a class="btn default btn-small margin-right-5 margin-bottom-5" href="https://xrdebug.com" target="_blank"><span class="fas fa-bug margin-right-5"></span>xrDebug</a>'
                ],
            ];

            $cheveretoLinks = [
                [
                    'label' => _s('Blog'),
                    'icon' => 'fas fa-blog',
                    'href' => 'https://blog.chevereto.com'
                ],
                [
                    'label' => _s('Docs'),
                    'icon' => 'fas fa-book',
                    'href' => $handler::var('docsBaseUrl')
                ],
                [
                    'label' => _s("Releases"),
                    'icon' => 'fas fa-rocket',
                    'href' => 'https://releases.chevereto.com'
                ],
                [
                    'label' => _s('Support'),
                    'icon' => 'fas fa-medkit',
                    'href' => 'https://chevereto.com/support'
                ],
                [
                    'label' => _s('Chat'),
                    'icon' => 'fas fa-comments',
                    'href' => 'https://chevereto.com/go/discord'
                ],
                [
                    'label' => _s('Community'),
                    'icon' => 'fas fa-users',
                    'href' => 'https://chevereto.com/community'
                ],
            ];
            $cheveretoLinksButtons = '';
            foreach ($cheveretoLinks as $link) {
                $attr = $link['attr'] ?? 'href="%href%" target="_blank"';
                $cheveretoLinksButtons .= strtr('<a ' . $attr . ' class="btn default btn-small margin-right-5 margin-bottom-5"><span class="btn-icon fa-btn-icon %icon%"></span><span class="btn-text">%label%</span></a>', [
                    '%href%' => $link['href'] ?? '',
                    '%icon%' => $link['icon'],
                    '%label%' => $link['label'],
                ]);
            }

            if (env()['CHEVERETO_CONTEXT'] !== 'saas') {
                $mysqlVersion = $db->getAttr(PDO::ATTR_SERVER_VERSION);
                $db->closeCursor();
                $mysqlServerInfo = $db->getAttr(PDO::ATTR_SERVER_INFO);
                $phpIniLoaded = php_ini_loaded_file();
                $phpIniFiles = php_ini_scanned_files() ?: 'N/A';
                $phpIniFiles = explode(',', $phpIniFiles);
                if ($phpIniLoaded) {
                    array_unshift($phpIniFiles, $phpIniLoaded);
                }
                $phpIniFiles = array_map(function ($v) {
                    return '<div data-click="select-all">' . $v . '</div>';
                }, $phpIniFiles);
                $phpIniFiles = implode('', $phpIniFiles);
                $system_values_more = [
                    'links' => [
                        'label' => _s('Links'),
                        'content' => $cheveretoLinksButtons,
                    ],
                    'cli' => [
                        'label' => 'CLI',
                        'content' => '<i class="fas fa-terminal"></i> <span data-click="select-all">' . PATH_PUBLIC . 'app/bin/legacy</span>',
                    ],
                    'cron' => [
                        'label' => _s('Cron last ran'),
                        'content' => '<i class="fas fa-clock"></i> ' . $cron_last_ran . ' UTC' . $cronRemark,
                    ],
                    'error_log' => [
                        'label' => 'Error log',
                        'content' => '<i class="fas fa-scroll"></i> <span data-click="select-all">' . Config::system()->errorLog() . '</span>' . $errorLogRemark,
                    ],
                    'server' => [
                        'label' => _s('Server'),
                        'content' => '<i class="fas fa-layer-group"></i> '
                            . (
                                (server()['SERVER_SOFTWARE'] ?? '🐍')
                            . ' ~ ' . gethostname()
                            . '<br>'
                            . PHP_OS
                            . '/'
                            . PHP_SAPI
                            . ((env()['CHEVERETO_SERVICING'] ?? null) === 'docker'
                                ? ' <span class="fab fa-docker"></span> Docker'
                                : '')
                            )
                    ],
                    'mysql' => [
                        'label' => 'MySQL',
                        'content' => '<i class="fas fa-database"></i> '
                            . $mysqlVersion
                            . '<br>'
                            . $mysqlServerInfo
                    ],
                    'php_version' => [
                        'label' => _s('PHP version'),
                        'content' => '<span class="fab fa-php"></span> '
                            . PHP_VERSION
                            . $phpIniFiles
                    ],
                    'file_uploads' => [
                        'label' => _s('File uploads'),
                        'content' => (int) ini_get('file_uploads') == 1
                            ? '<i class="fas fa-check-circle"></i> ' . _s('Enabled')
                            : '<i class="fas fa-times color-fail"></i> ' . _s('Disabled')
                    ],
                    'max_execution_time' => [
                        'label' => _s('Max. execution time'),
                        'content' => '<i class="fas fa-stopwatch"></i> ' . strtr(_n('%d second', '%d seconds', ini_get('max_execution_time')), ['%d' => ini_get('max_execution_time')])
                    ],
                    'memory_limit' => [
                        'label' => _s('Memory limit'),
                        'content' => '<i class="fas fa-memory"></i> ' . format_bytes(get_ini_bytes(ini_get('memory_limit')))
                    ],
                ];
                $pos = array_search('max_upload_size', array_keys($system_values), true);
                array_splice($system_values, $pos, 0, $system_values_more);
            }

            $graphicsLibraryContent = '<i class="fas fa-feather"></i> ';
            if (ImageManagerStatic::getManager()->config['driver'] === 'imagick') {
                $graphicVersion = env()['CHEVERETO_CONTEXT'] === 'saas'
                    ? "ImageMagick"
                    : Imagick::getVersion()['versionString'];
                $system_values['graphics']['content'] = $graphicsLibraryContent . $graphicVersion;
            } else {
                $graphicVersion = env()['CHEVERETO_CONTEXT'] === 'saas'
                    ? 'GD '
                    : 'GD Version ' . gd_info()['GD Version'];
                $system_values['graphics']['content'] = $graphicsLibraryContent . $graphicVersion
                    . ' JPEG:' . gd_info()['JPEG Support']
                    . ' GIF:' . gd_info()['GIF Read Support'] . '/' . gd_info()['GIF Create Support']
                    . ' PNG:' . gd_info()['PNG Support']
                    . ' WEBP:' . (gd_info()['WebP Support'] ?? 0)
                    . ' WBMP:' . gd_info()['WBMP Support']
                    . ' XBM:' . gd_info()['XBM Support'];
            }
            $handler::setVar('system_values', $system_values);
            $handler::setVar('totals', $totals);
            $handler::setVar('totals_display', $totals_display);

            break;

        case 'settings':
            $max_request_level = ($handler->request()[1] ?? null) == 'pages' ? (in_array($handler->request()[2] ?? null, ['edit', 'delete']) ? 6 : 5) : 4;
            if ($handler->isRequestLevel($max_request_level)) {
                $handler->issueError(404);

                return;
            }
            $handler::setCond('show_submit', true);
            $settingsSectionsTitles = $settings_sections;
            if ((bool) env()['CHEVERETO_ENABLE_EXPOSE_PAID_FEATURES']) {
                foreach ($paidSettingsEnv as $k => $v) {
                    $isEnabled = in_array($v[0], editionCombo()[env()['CHEVERETO_EDITION']]);
                    if (!$isEnabled) {
                        array_push($paidSettings, $k);
                        $settings_sections[$k] .= ' ' . badgePaid($v[0]);
                    }
                }
            }
            foreach ($settings_sections as $k => $v) {
                $current = ($handler->request()[1] ?? null) ? ($handler->request()[1] == $k) : ($k == 'website');
                $isPaidSetting = in_array($k, $paidSettings);
                $settings_sections[$k] = [
                    'icon' => $settings_sections_icons[$k],
                    'key' => $k,
                    'label' => $v,
                    'title' => $settingsSectionsTitles[$k],
                    'url' => $isPaidSetting
                      ? 'https://chevereto.com/pricing'
                      : get_base_url($route_prefix . '/settings/' . $k),
                    'current' => $current,
                    'isPaid' => $isPaidSetting,
                ];
                if ($current) {
                    $handler::setVar('settings', $settings_sections[$k]);
                    if (in_array($k, ['categories', 'ip-bans'])) {
                        $handler::setCond('show_submit', false);
                    }
                }
            }
            if (!empty($handler->request()[1]) && !array_key_exists($handler->request()[1], $settings_sections)) {
                $handler->issueError(404);

                return;
            }
            $handler::setVar('settings_menu', $settings_sections);
            if (isset($handler->request()[1])) {
                $requestSetting = $handler->request()[1];
                if (in_array($requestSetting, $paidSettings)) {
                    $requestSetting = '';
                    $handler->issueError(404);

                    return;
                }
                switch ($requestSetting) {
                    case 'homepage':
                        if ((get()['action'] ?? '') == 'delete-cover' && isset(get()['cover'])) {
                            if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
                                $handler->issueError(403);

                                return;
                            }
                            $cover_index = get()['cover'] - 1;
                            $homecovers = getSetting('homepage_cover_images');
                            $cover_target = $homecovers[$cover_index];
                            if (!is_integer(get()['cover']) || !isset($cover_target)) {
                                $is_error = true;
                                $error_message = _s('Request denied');
                            }
                            if (is_array($homecovers) && count($homecovers) == 1) {
                                $is_error = true;
                                $input_errors[sprintf('homepage_cover_image_%s', $cover_index)] = _s("Can't delete all homepage cover images");
                            }
                            if (!$is_error) {
                                // Try to delete the image (disk)
                                if (!starts_with('default/', $cover_target['basename'])) {
                                    $cover_file = PATH_PUBLIC_CONTENT_IMAGES_SYSTEM . $cover_target['basename'];
                                    $storagePath = ltrim(absolute_to_relative($cover_file), '/');
                                    AssetStorage::deleteFiles(['key' => $storagePath]);
                                }
                                unset($homecovers[$cover_index]);
                                $homecovers = array_values($homecovers);
                                $homecovers_db = [];
                                foreach ($homecovers as $v) {
                                    $homecovers_db[] = $v['basename'];
                                }
                                Settings::update(['homepage_cover_image' => implode(',', $homecovers_db)]);
                                sessionVar()->put('is_changed', true);
                                redirect('dashboard/settings/homepage');
                            }
                        }
                        if (session()['is_changed'] ?? false) {
                            $is_changed = true;
                            $changed_message = _s('Homepage cover image deleted');
                            sessionVar()->remove('is_changed');
                        }

                        break;

                    case 'tools':
                        $handler::setCond('show_submit', false);

                        break;

                    case 'external-storage':
                        $disk_used_all = Stat::getTotals()['disk_used'];
                        $disk_used_external = DB::queryFetchSingle('SELECT SUM(storage_space_used) space_used FROM ' . DB::getTable('storages') . ';')['space_used'];
                        $storage_usage = [
                            'local' => [
                                'label' => _s('Local'),
                                'bytes' => $disk_used_all - $disk_used_external
                            ],
                            'external' => [
                                'label' => _s('External'),
                                'bytes' => $disk_used_external
                            ]
                        ];
                        $storage_usage['all'] = [
                            'label' => _s('All'),
                            'bytes' => $storage_usage['local']['bytes'] + $storage_usage['external']['bytes']
                        ];
                        foreach ($storage_usage as $k => &$v) {
                            if (empty($v['bytes'])) {
                                $v['bytes'] = 0;
                            }
                            $v['link'] = '<a class="btn btn-small default" href="' . get_base_url('search/images/?q=storage:' . $k) . '" target="_blank"><i class="fas fa-search margin-right-5"></i>' . _s('search content') . '</a>';
                            $v['formatted_size'] = format_bytes($v['bytes'], 2);
                        }

                        $handler::setVar('storage_usage', $storage_usage);

                        break;

                    case 'pages':
                        $settings_pages = [];
                        // Check the sub-request
                        if (isset($handler->request()[2])) {
                            switch ($handler->request()[2]) {
                                case 'add':
                                    $settings_pages['title'] = _s('Add page');
                                    $settings_pages['doing'] = 'add';

                                    break;
                                case 'edit':
                                case 'delete':
                                    if (!filter_var($handler->request()[3], FILTER_VALIDATE_INT)) {
                                        $handler->issueError(404);

                                        return;
                                    }
                                    $page = Page::getSingle($handler->request()[3], 'id');
                                    if ($page) {
                                        // Workaround for default pages
                                        if (starts_with('default/', $page['file_path'])) {
                                            $page['file_path'] = null;
                                        }
                                    } else {
                                        $handler->issueError(404);

                                        return;
                                    }
                                    $handler::setvar('page', $page);
                                    if ($handler->request()[2] == 'edit') {
                                        $settings_pages['title'] = '<i class="fas fa-edit"></i> ' . _s('Edit page ID %s', $page['id']);
                                        $settings_pages['doing'] = 'edit';
                                        if (session()['dashboard_page_added'] ?? false) {
                                            if (isset(session()['dashboard_page_added']['id']) && session()['dashboard_page_added']['id'] == $page['id']) {
                                                $is_changed = true;
                                                $changed_message = _s('The page has been added successfully.');
                                            }
                                            sessionVar()->remove('dashboard_page_added');
                                        }
                                    }
                                    if ($handler->request()[2] == 'delete') {
                                        if (!$handler::checkAuthToken(request()['auth_token'] ?? '')) {
                                            $handler->issueError(403);

                                            return;
                                        }
                                        Page::delete($page);
                                        sessionVar()->put('dashboard_page_deleted', ['id' => $page['id']]);
                                        redirect('dashboard/settings/pages');
                                    }

                                    break;
                                default:
                                    $handler->issueError(404);

                                    return;
                            }
                        } else {
                            $pages = Page::getAll([], ['field' => 'sort_display', 'order' => 'asc']);
                            $handler::setVar('pages', $pages);
                            $settings_pages['doing'] = 'listing';
                            if (session()['dashboard_page_deleted'] ?? false) {
                                $is_changed = true;
                                $changed_message = _s('The page has been deleted.');
                                sessionVar()->remove('dashboard_page_deleted');
                            }
                            $handler::setCond('show_submit', false);
                        }

                        $handler::setvar('settings_pages', $settings_pages);

                        break;

                    case 'banners':
                        $stock_banners = [
                            'home' => [
                                'label' => _s('Homepage'),
                                'placements' => [
                                    'banner_home_before_title' => [
                                        'label' => _s('Before main title (%s)', _s('homepage'))
                                    ],
                                    'banner_home_after_cta' => [
                                        'label' => _s('After call to action (%s)', _s('homepage'))
                                    ],
                                    'banner_home_after_cover' => [
                                        'label' => _s('After cover (%s)', _s('homepage'))
                                    ],
                                    'banner_home_after_listing' => [
                                        'label' => _s('After listing (%s)', _s('homepage'))
                                    ]
                                ]
                            ],
                            'listing' => [
                                'label' => _s('Listings'),
                                'placements' => [
                                    'banner_listing_before_pagination' => [
                                        'label' => _s('Before pagination'),
                                    ],
                                    'banner_listing_after_pagination' => [
                                        'label' => _s('After pagination'),
                                    ]
                                ]
                            ],
                            'content' => [
                                'label' => _s('Content (image and album)'),
                                'placements' => [
                                    'banner_content_tab-about_column' => [
                                        'label' => _s('Tab about column')
                                    ],
                                    'banner_content_before_comments' => [
                                        'label' => _s('Before comments')
                                    ]
                                ]
                            ],
                            'image' => [
                                'label' => _s('Image page'),
                                'placements' => [
                                    'banner_image_image-viewer_top' => [
                                        'label' => _s('Inside viewer top (image page)'),
                                    ],
                                    'banner_image_image-viewer_foot' => [
                                        'label' => _s('Inside viewer foot (image page)'),
                                    ],
                                    'banner_image_after_image-viewer' => [
                                        'label' => _s('After image viewer (image page)')
                                    ],
                                    'banner_image_before_header' => [
                                        'label' => _s('Before header (image page)')
                                    ],
                                    'banner_image_after_header' => [
                                        'label' => _s('After header (image page)')
                                    ],
                                    'banner_image_footer' => [
                                        'label' => _s('Footer (image page)')
                                    ]
                                ]
                            ],
                            'album' => [
                                'label' => _s('%s page', _n('Album', 'Albums', 1)),
                                'placements' => [
                                    'banner_album_before_header' => [
                                        'label' => _s('Before header (%s page)', _n('Album', 'Albums', 1))
                                    ],
                                    'banner_album_after_header' => [
                                        'label' => _s('After header (%s page)', _n('Album', 'Albums', 1))
                                    ]
                                ]
                            ],
                            'user' => [
                                'label' => _s('User profile page'),
                                'placements' => [
                                    'banner_user_after_top' => [
                                        'label' => _s('After top (user profile)')
                                    ],
                                    'banner_user_before_listing' => [
                                        'label' => _s('Before listing (user profile)')
                                    ]
                                ]
                            ],
                            'explore' => [
                                'label' => _s('%s page', _s('Explore')),
                                'placements' => [
                                    'banner_explore_after_top' => [
                                        'label' => _s('After top (%s page)', _s('Explore'))
                                    ]
                                ]
                            ]
                        ];
                        $banners = [];
                        foreach ($stock_banners as $k => $stock_group) {
                            $banners[$k] = $stock_group;
                            $group_nsfw = [
                                'label' => $stock_group['label'] . ' [' . _s('NSFW') . ']',
                                'placements' => []
                            ];
                            foreach ($stock_group['placements'] as $id => $placement) {
                                $group_nsfw['placements'][$id . '_nsfw'] = $placement;
                            }
                            $banners[$k . '_nsfw'] = $group_nsfw;
                        }
                        $handler::setVar('banners', $banners);

                        break;
                }
            }
            if ($POST !== []) {
                if (!headers_sent()) {
                    header('X-XSS-Protection: 0');
                }
                if (isset($POST['theme_home_uids'])) {
                    $POST['theme_home_uids'] = implode(',', array_keys(array_flip(explode(',', trim(preg_replace(['/\s+/', '/,+/'], ['', ','], $POST['theme_home_uids']), ',')))));
                }
                if (isset($POST['website_mode']) && $POST['website_mode'] == 'personal') {
                    $POST['website_mode_personal_routing'] = get_regex_match(
                        getSetting('routing_regex'),
                        $POST['website_mode_personal_routing'],
                        '#',
                        1
                    );

                    if (!check_value($POST['website_mode_personal_routing'])) {
                        $POST['website_mode_personal_routing'] = '/';
                    }
                }
                if (!empty($POST['homepage_cta_fn_extra'])) {
                    $POST['homepage_cta_fn_extra'] = trim($POST['homepage_cta_fn_extra']);
                }
                foreach (['phone', 'phablet', 'laptop', 'desktop'] as $k) {
                    if (isset($POST['listing_columns_' . $k])) {
                        $key = 'listing_columns_' . $k;
                        $val = $POST[$key];
                        $POST[$key] = (filter_var($val, FILTER_VALIDATE_INT) && $val > 0) ? $val : get_chv_default_setting($key);
                    }
                }
                if (($handler->request()[1] ?? null) == 'pages') {
                    $page_file_path_clean = trim(sanitize_relative_path($POST['page_file_path']), '/');
                    $POST['page_file_path'] = str_replace('default/', '', $page_file_path_clean);
                    $POST['page_file_path_absolute'] = Page::getPath($POST['page_file_path']);
                    if (!filter_var($POST['page_sort_display'], FILTER_VALIDATE_INT)) {
                        $POST['page_sort_display'] = null;
                    }
                    if (isset($POST['page_type']) && $POST['page_type'] == 'internal') {
                        if (!$POST['page_is_active']) {
                            $POST['page_is_link_visible'] = false;
                        }
                    } else {
                        $POST['page_is_link_visible'] = $POST['page_is_active'];
                    }
                    $handler::updateVar('safe_post', [
                        'page_is_active' => $POST['page_is_active'],
                        'page_is_link_visible' => $POST['page_is_link_visible'],
                        'page_file_path_absolute' => $POST['page_file_path_absolute'],
                    ]);
                }
                $validations = [
                    'website_name' =>
                    [
                        'validate' => isset($POST['website_name']),
                        'error_msg' => _s('Invalid website name')
                    ],
                    'default_language' =>
                    [
                        'validate' => isset($POST['default_language']) && get_available_languages()[$POST['default_language']] !== null,
                        'error_msg' => _s('Invalid language')
                    ],
                    'default_timezone' =>
                    [
                        'validate' => isset($POST['default_timezone']) && in_array($POST['default_timezone'], timezone_identifiers_list()),
                        'error_msg' => _s('Invalid timezone')
                    ],
                    'listing_items_per_page' =>
                    [
                        'validate' => isset($POST['listing_items_per_page']) && is_integer($POST['listing_items_per_page']),
                        'error_msg' => _s('Invalid value: %s', $POST['listing_items_per_page'] ?? '')
                    ],
                    'explore_albums_min_image_count' =>
                    [
                        'validate' => isset($POST['explore_albums_min_image_count']) && is_integer($POST['explore_albums_min_image_count']),
                        'error_msg' => _s('Invalid value: %s', $POST['explore_albums_min_image_count'] ?? '')
                    ],
                    'upload_threads' =>
                    [
                        'validate' => isset($POST['upload_threads']) && is_integer($POST['upload_threads']),
                        'error_msg' => _s('Invalid value: %s', $POST['upload_threads'] ?? '')
                    ],
                    'upload_storage_mode' =>
                    [
                        'validate' => isset($POST['upload_storage_mode']) && in_array($POST['upload_storage_mode'], ['datefolder', 'direct']),
                        'error_msg' => _s('Invalid upload storage mode')
                    ],
                    'upload_filenaming' =>
                    [
                        'validate' => isset($POST['upload_filenaming']) && in_array($POST['upload_filenaming'], ['original', 'random', 'mixed', 'id']),
                        'error_msg' => _s('Invalid upload filenaming')
                    ],
                    'upload_thumb_width' =>
                    [
                        'validate' => isset($POST['upload_thumb_width']) && is_integer($POST['upload_thumb_width']),
                        'error_msg' => _s('Invalid thumb width')
                    ],
                    'upload_thumb_height' =>
                    [
                        'validate' => isset($POST['upload_thumb_height']) && is_integer($POST['upload_thumb_height']),
                        'error_msg' => _s('Invalid thumb height')
                    ],
                    'upload_medium_size' =>
                    [
                        'validate' => isset($POST['upload_medium_size']) && is_integer($POST['upload_medium_size']),
                        'error_msg' => _s('Invalid medium size')
                    ],
                    'watermark_percentage' =>
                    [
                        'validate' => isset($POST['watermark_percentage']) && is_integer($POST['watermark_percentage']),
                        'error_msg' => _s('Invalid watermark percentage')
                    ],
                    'watermark_opacity' =>
                    [
                        'validate' => isset($POST['watermark_opacity']) && is_integer($POST['watermark_opacity']),
                        'error_msg' => _s('Invalid watermark opacity')
                    ],
                    'theme' =>
                    [
                        'validate' => isset($POST['theme']) && file_exists(PATH_PUBLIC_CONTENT_LEGACY_THEMES . $POST['theme']),
                        'error_msg' => _s('Invalid theme')
                    ],
                    'theme_logo_height' =>
                    [
                        'validate' => empty($POST['theme_logo_height']) ? (true) : is_integer($POST['theme_logo_height']),
                        'error_msg' => _s('Invalid value')
                    ],
                    'theme_font' =>
                    [
                        'validate' => isset($POST['theme_font']) && in_array(
                            $POST['theme_font'],
                            array_keys($handler::var('fonts')->get())
                        ),
                        'error_msg' => _s('Invalid %s', _s('font'))
                    ],
                    'theme_palette' =>
                    [
                        'validate' => isset($POST['theme_palette']) && in_array(
                            $POST['theme_palette'],
                            array_keys($handler::var('palettes')->get())
                        ),
                        'error_msg' => _s('Invalid %s', _s('palette'))
                    ],
                    'theme_image_listing_sizing' =>
                    [
                        'validate' => isset($POST['theme_image_listing_sizing']) && in_array($POST['theme_image_listing_sizing'], ['fluid', 'fixed']),
                        'error_msg' => _s('Invalid theme image listing size')
                    ],
                    'theme_home_uids' =>
                    [
                        'validate' => empty($POST['theme_home_uids']) ? true : preg_match('/^\d+(\,\d+)*$/', $POST['theme_home_uids']),
                        'error_msg' => _s('Invalid user id')
                    ],
                    'email_mode' =>
                    [
                        'validate' => isset($POST['email_mode']) && in_array($POST['email_mode'], ['smtp', 'mail']),
                        'error_msg' => _s('Invalid email mode')
                    ],
                    'email_smtp_server_port' =>
                    [
                        'validate' => isset($POST['email_smtp_server_port']) && in_array($POST['email_smtp_server_port'], [25, 80, 465, 587]),
                        'error_msg' => _s('Invalid SMTP port')
                    ],
                    'email_smtp_server_security' =>
                    [
                        'validate' => isset($POST['email_smtp_server_security']) && in_array($POST['email_smtp_server_security'], ['tls', 'ssl', 'unsecured']),
                        'error_msg' => _s('Invalid SMTP security')
                    ],
                    'website_mode' =>
                    [
                        'validate' => isset($POST['website_mode']) && in_array($POST['website_mode'], ['community', 'personal']),
                        'error_msg' => _s('Invalid website mode')
                    ],
                    'website_mode_personal_uid' =>
                    [
                        'validate' => isset($POST['website_mode'], $POST['website_mode_personal_uid']) && $POST['website_mode'] == 'personal' ? (is_integer($POST['website_mode_personal_uid'])) : true,
                        'error_msg' => _s('Invalid personal mode user ID')
                    ],
                    'website_mode_personal_routing' =>
                    [
                        'validate' => isset($POST['website_mode'], $POST['website_mode_personal_routing']) && $POST['website_mode'] == 'personal' ? !is_route_available($POST['website_mode_personal_routing']) : true,
                        'error_msg' => _s('Invalid or reserved route')
                    ],
                    'website_privacy_mode' =>
                    [
                        'validate' => isset($POST['website_privacy_mode']) && in_array($POST['website_privacy_mode'], ['public', 'private']),
                        'error_msg' => _s('Invalid website privacy mode')
                    ],
                    'website_content_privacy_mode' =>
                    [
                        'validate' => isset($POST['website_content_privacy_mode']) && in_array($POST['website_content_privacy_mode'], ['default', 'private', 'private_but_link']),
                        'error_msg' => _s('Invalid website content privacy mode')
                    ],
                    'homepage_style' =>
                    [
                        'validate' => isset($POST['homepage_style']) && in_array($POST['homepage_style'], ['landing', 'split', 'route_explore', 'route_upload']),
                        'error_msg' => _s('Invalid homepage style')
                    ],
                    'homepage_cta_color' =>
                    [
                        'validate' => isset($POST['homepage_cta_color']) && in_array($POST['homepage_cta_color'], getSetting('available_button_colors')),
                        'error_msg' => _s('Invalid homepage call to action button color')
                    ],
                    'homepage_cta_fn' =>
                    [
                        'validate' => (isset($POST['homepage_style'], $POST['homepage_cta_fn'])
                            ? ($POST['homepage_style'] == 'route_explore' ? true : in_array($POST['homepage_cta_fn'], ['cta-upload', 'cta-link']))
                            : false),
                        'error_msg' => _s('Invalid homepage call to action functionality')
                    ],
                    'page_title' =>
                    [
                        'validate' => !empty($POST['page_title']),
                        'error_msg' => _s('Invalid title')
                    ],
                    'page_is_active' =>
                    [
                        'validate' => isset($POST['page_is_active']) && in_array($POST['page_is_active'], ['1', '0']),
                        'error_msg' => _s('Invalid status')
                    ],
                    'page_type' =>
                    [
                        'validate' => isset($POST['page_type']) && in_array($POST['page_type'], ['internal', 'link']),
                        'error_msg' => _s('Invalid type')
                    ],
                    'page_is_link_visible' =>
                    [
                        'validate' => isset($POST['page_type'], $POST['page_is_link_visible']) && $POST['page_type'] == 'internal' ? in_array($POST['page_is_link_visible'], ['1', '0']) : true,
                        'error_msg' => _s('Invalid visibility')
                    ],
                    'page_internal' =>
                    [
                        'validate' => isset($POST['page_type'], $POST['page_internal']) && ($POST['page_type'] == 'internal' && $POST['page_internal']) ? in_array($POST['page_internal'], ['tos', 'privacy', 'contact']) : true,
                        'error_msg' => _s('Invalid internal type')
                    ],
                    'page_attr_target' =>
                    [
                        'validate' => isset($POST['page_attr_target']) && in_array($POST['page_attr_target'], ['_self', '_blank']),
                        'error_msg' => _s('Invalid target attribute')
                    ],
                    'page_attr_rel' =>
                    [
                        'validate' => empty($POST['page_attr_rel']) ? true : preg_match('/^[\w\s\-]+$/', $POST['page_attr_rel']),
                        'error_msg' => _s('Invalid rel attribute')
                    ],
                    'page_icon' =>
                    [
                        'validate' => empty($POST['page_icon']) ? true : preg_match('/^[\w\s\-]+$/', $POST['page_icon']),
                        'error_msg' => _s('Invalid icon')
                    ],
                    'page_url_key' =>
                    [
                        'validate' => isset($POST['page_type'], $POST['page_url_key']) && $POST['page_type'] == 'internal' ? preg_match('/^[\w\-\_\/]+$/', $POST['page_url_key']) : true,
                        'error_msg' => _s('Invalid URL key')
                    ],
                    'page_file_path' =>
                    [
                        'validate' => isset($POST['page_type'], $POST['page_file_path']) && $POST['page_type'] == 'internal' ? preg_match('/^[\w\-\_\/]+\.' . (Config::enabled()->phpPages() ? 'html|php' : 'html') . '$/', $POST['page_file_path']) : true,
                        'error_msg' => _s('Invalid file path')
                    ],
                    'page_link_url' =>
                    [
                        'validate' => isset($POST['page_type'], $POST['page_link_url']) && $POST['page_type'] == 'link' ? is_url_web($POST['page_link_url']) : true,
                        'error_msg' => _s('Invalid link URL')
                    ],
                    'user_minimum_age' =>
                    [
                        'validate' => isset($POST['user_minimum_age']) && $POST['user_minimum_age'] !== '' ? is_integer($POST['user_minimum_age']) : true,
                        'error_msg' => _s('Invalid user minimum age')
                    ],
                    'route_image' =>
                    [
                        'validate' => isset($POST['route_image']) && preg_match('/^[\w\d\-\_]+$/', $POST['route_image']),
                        'error_msg' => _s('Only alphanumeric, hyphen and underscore characters are allowed')
                    ],
                    'route_album' =>
                    [
                        'validate' => isset($POST['route_album']) && preg_match('/^[\w\d\-\_]+$/', $POST['route_album']),
                        'error_msg' => _s('Only alphanumeric, hyphen and underscore characters are allowed')
                    ],
                    'route_user' =>
                    [
                        'validate' => isset($POST['route_user']) && preg_match('/^[\w\d\-\_]+$/', $POST['route_user']),
                        'error_msg' => _s('Only alphanumeric, hyphen and underscore characters are allowed')
                    ],
                    'image_load_max_filesize_mb' =>
                    [
                        'validate' => isset($POST['image_load_max_filesize_mb']) && $POST['image_load_max_filesize_mb'] !== '' ? is_numeric($POST['image_load_max_filesize_mb']) : true,
                        'error_msg' => _s('Invalid value: %s', $POST['image_load_max_filesize_mb'] ?? '')
                    ],
                    'upload_max_image_width' =>
                    [
                        'validate' => isset($POST['upload_max_image_width']) && is_integer($POST['upload_max_image_width']),
                        'error_msg' => _s('Invalid value: %s', $POST['upload_max_image_width'] ?? '')
                    ],
                    'upload_max_image_height' =>
                    [
                        'validate' => isset($POST['upload_max_image_height']) && is_integer($POST['upload_max_image_height']),
                        'error_msg' => _s('Invalid value: %s', $POST['upload_max_image_height'] ?? '')
                    ],
                    'auto_delete_guest_uploads' =>
                    [
                        'validate' => ($POST['auto_delete_guest_uploads'] ?? '') !== ''
                            ? array_key_exists($POST['auto_delete_guest_uploads'], Image::getAvailableExpirations())
                            : true,
                        'error_msg' => _s('Invalid value: %s', $POST['auto_delete_guest_uploads'] ?? 'empty string')
                    ],
                    'sdk_pup_url' =>
                    [
                        'validate' => isset($POST['sdk_pup_url']) && $POST['sdk_pup_url'] ? is_url_web($POST['sdk_pup_url']) : true,
                        'error_msg' => _s('Invalid URL')
                    ],
                    'akismet_api_key' => [
                        'validate' => ($POST['akismet'] ?? 0) == 1 && isset($POST['akismet_api_key'])
                            ? $POST['akismet_api_key'] !== ''
                            : true,
                        'error_msg' => _s('Invalid key'),
                    ],
                    'moderatecontent_key' => [
                        'validate' => ($POST['moderatecontent'] ?? 0) == 1 && isset($POST['moderatecontent_key'])
                            ? $POST['moderatecontent_key'] !== ''
                            : true,
                        'error_msg' => _s('Invalid key'),
                    ],
                    'arachnid_key' => [
                        'validate' => ($POST['arachnid'] ?? 0) == 1 && isset($POST['arachnid_key'])
                            ? $POST['arachnid_key'] !== ''
                            : true,
                        'error_msg' => _s('Invalid key'),
                    ]
                ];
                $customRoutes = [];
                foreach (['image', 'album', 'user'] as $test) {
                    $tryValue = $POST['route_' . $test] ?? null;
                    if ($tryValue !== null) {
                        if (in_array($tryValue, $customRoutes)) {
                            $validations['route_' . $test] = [
                                'validate' => false,
                                'error_msg' => _s("Routes can't be the same")
                            ];
                        } else {
                            $customRoutes[] = $tryValue;
                        }
                    }
                }
                if (($handler->request()[1] ?? null) === 'login-providers') {
                    $loginProviders = Login::getProviders('all');
                    foreach ($loginProviders as $providerName => $provider) {
                        $validate = in_array($POST[$providerName] ?? '', ['0', '1']);
                        if (!$validate) {
                            $validations[$v] = ['validate' => $validate];

                            continue;
                        }
                        $stored = [
                            'key_id' => $provider['key_id'] ?? '',
                            'key_secret' => $provider['key_secret'] ?? '',
                            'is_enabled' => $provider['is_enabled']
                        ];
                        $compare = [
                            'key_id' => $POST[$providerName . '_id'] ?? '',
                            'key_secret' => $POST[$providerName . '_secret'] ?? '',
                            'is_enabled' => (bool) ($POST[$providerName] ?? false),
                        ];
                        if ($stored !== $compare) {
                            Login::updateProvider($providerName, $compare);
                            $providersChange[] = $providerName;
                        }
                    }
                }
                if (isset($POST['upload_image_path'])) {
                    $safe_upload_image_path = rtrim(sanitize_relative_path($POST['upload_image_path']), '/');
                    $image_path = PATH_PUBLIC . $POST['upload_image_path'];
                    if (!file_exists($image_path)) {
                        $validations['upload_image_path'] = [
                            'validate' => false,
                            'error_msg' => _s('Invalid upload image path')
                        ];
                    }
                }
                if (isset($POST['homepage_style']) && $POST['homepage_style'] !== 'route_explore' && $POST['homepage_cta_fn'] == 'cta-link' && !is_url($POST['homepage_cta_fn_extra'])) {
                    if (!empty($POST['homepage_cta_fn_extra'])) {
                        $POST['homepage_cta_fn_extra'] = rtrim(sanitize_relative_path($POST['homepage_cta_fn_extra']), '/');
                        $POST['homepage_cta_fn_extra'] = get_regex_match(
                            getSetting('routing_regex_path'),
                            $POST['homepage_cta_fn_extra'],
                            '#',
                            1
                        );
                    } else {
                        $validations['homepage_cta_fn_extra'] = [
                            'validate' => false,
                            'error_msg' => _s('Invalid call to action URL')
                        ];
                    }
                }
                foreach (['upload_max_filesize_mb', 'upload_max_filesize_mb_guest', 'user_image_avatar_max_filesize_mb', 'user_image_background_max_filesize_mb'] as $k) {
                    unset($error_max_filesize);
                    if (isset($POST[$k])) {
                        if (!is_numeric($POST[$k]) || $POST[$k] == 0) {
                            $error_max_filesize = _s('Invalid value');
                        } elseif (get_bytes($POST[$k] . 'MB') > Settings::get('true_upload_max_filesize')) {
                            $error_max_filesize = _s('Max. allowed %s', format_bytes(Settings::get('true_upload_max_filesize')));
                        }
                        $validations[$k] = ['validate' => !isset($error_max_filesize), 'error_msg' => $error_max_filesize ?? ''];
                    }
                }
                foreach (['image', 'album'] as $k) {
                    $route = 'route_' . $k;
                    if (!isset($POST[$route])) {
                        continue;
                    }
                    if (file_exists(PATH_PUBLIC . $POST[$route])) {
                        $validations[$route] = [
                            'validate' => false,
                            'error_msg' => _s("Can't map %m to an existing folder (%f)", ['%m' => '/' . $k, '%f' => '/' . $POST[$route]])
                        ];

                        continue;
                    }
                    if (isset($POST[$route]) && $POST[$route] !== $k && $validations[$route]['validate']) {
                        if (is_route_available($POST[$route])) {
                            $validations[$route] = [
                                'validate' => false,
                                'error_msg' => _s("Can't map %m to an existing route (%r)", ['%m' => '/' . $k, '%r' => '/' . $POST[$route]])
                            ];
                        } else {
                            $user_exists = User::getSingle($POST[$route], 'username', false);
                            if ($user_exists !== []) {
                                $validations[$route] = [
                                    'validate' => false,
                                    'error_msg' => _s("Can't map %m to %r (username collision)", ['%m' => '/' . $k, '%r' => '/' . $POST[$route]])
                                ];
                            }
                        }
                    }
                }
                if (isset($POST['image_format_enable']) && is_array($POST['image_format_enable'])) {
                    $image_format_enable = [];
                    foreach ($POST['image_format_enable'] as $v) {
                        if (in_array($v, Upload::getAvailableImageFormats())) {
                            $image_format_enable[] = $v;
                        }
                    }
                    $POST['upload_enabled_image_formats'] = implode(',', $image_format_enable);
                }
                if (isset($POST['default_language'])
                    && ($POST['language_chooser_enable'] ?? 0) == 0
                    && array_key_exists($POST['default_language'], L10n::getEnabledLanguages())
                ) {
                    L10n::processTranslation($POST['default_language']);
                }
                if (isset($POST['languages_enable']) && is_array($POST['languages_enable'])) {
                    if (!in_array($POST['default_language'], $POST['languages_enable'])) {
                        $POST['languages_enable'][] = $POST['default_language'];
                    }
                    $enabled_languages = [];
                    $disabled_languages = get_available_languages();
                    $POST['languages_disable'] = [];
                    foreach ($POST['languages_enable'] as $k) {
                        if (!array_key_exists($k, get_available_languages())) {
                            continue;
                        }
                        $enabled_languages[$k] = get_available_languages()[$k];
                        unset($disabled_languages[$k]);
                    }
                    L10n::setStatic('disabled_languages', $disabled_languages);
                    L10n::setStatic('enabled_languages', $enabled_languages);
                    unset($POST['languages_enable']);
                    foreach (array_keys($disabled_languages) as $k) {
                        $POST['languages_disable'][] = $k;
                    }
                    $POST['languages_disable'] = implode(',', $POST['languages_disable']);
                }
                if (isset($POST['website_mode']) && $POST['website_mode'] == 'personal' && isset($POST['website_mode_personal_routing'])) {
                    if ($logged_user['id'] == $POST['website_mode_personal_uid']) {
                        $new_user_url = get_base_url($POST['website_mode_personal_routing'] !== '/' ? $POST['website_mode_personal_routing'] : '');
                        Login::setUser('url', get_base_url($POST['website_mode_personal_routing'] !== '/' ? $POST['website_mode_personal_routing'] : ''));
                        Login::setUser('url_albums', User::getUrlAlbums(Login::getUser()['url']));
                    } elseif (User::getSingle($POST['website_mode_personal_uid']) === []) { // Is a valid user id anyway?
                        $validations['website_mode_personal_uid'] = [
                            'validate' => false,
                            'error_msg' => _s('Invalid personal mode user ID')
                        ];
                    }
                }
                $content_image_props = [];
                foreach (getSetting('homepage_cover_images') ?? [] as $k => $v) {
                    $content_image_props[] = sprintf('homepage_cover_image_%s', $k);
                }
                $content_image_props = array_merge($content_image_props, ['logo_vector', 'logo_image', 'favicon_image', 'watermark_image', 'consent_screen_cover_image', 'homepage_cover_image_add']);
                foreach ($content_image_props as $k) {
                    if (!empty(files()[$k]['tmp_name'])) {
                        try {
                            upload_to_content_images(files()[$k], $k);
                        } catch (Throwable $e) {
                            $validations[$k] = [
                                'validate' => false,
                                'error_msg' => $e->getMessage(),
                            ];
                        }
                    }
                }

                if (isset($POST['moderatecontent']) && $POST['moderatecontent'] == 1) {
                    $moderateContentKey = getSetting('moderatecontent_key');
                    if (isset($POST['moderatecontent_key'])) {
                        $moderateContentKey = $POST['moderatecontent_key'];
                    }
                    $sample = 'http://www.moderatecontent.com/img/sample_face_2.jpg';
                    $json = fetch_url('https://api.moderatecontent.com/moderate/?key=' . $moderateContentKey . '&url=' . $sample);
                    $data = json_decode($json);
                    if (property_exists($data, 'error') && $data->error !== null) {
                        $validations['moderatecontent_key'] = [
                            'validate' => false,
                            'error_msg' => $data->error
                        ];
                    }
                }

                if (isset($POST['arachnid']) && $POST['arachnid'] == 1) {
                    $arachnidKey = getSetting('arachnid_key');
                    if (isset($POST['arachnid_key'])) {
                        $arachnidKey = $POST['arachnid_key'];
                    }
                    $sample = PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'favicon.png';
                    $arachnid = new Arachnid($arachnidKey, $sample);
                    if (!$arachnid->isSuccess()) {
                        $validations['arachnid_key'] = [
                            'validate' => false,
                            'error_msg' => $arachnid->errorMessage()
                        ];
                    }
                }

                if (isset($POST['email_mode']) && $POST['email_mode'] == 'smtp') {
                    $email_smtp_validate = [
                        'email_smtp_server' => _s('Invalid SMTP server'),
                        'email_smtp_server_username' => _s('Invalid SMTP username'),
                    ];
                    foreach ($email_smtp_validate as $k => $v) {
                        $validations[$k] = ['validate' => (bool) $POST[$k], 'error_msg' => $v];
                    }

                    $email_validate = ['email_smtp_server', 'email_smtp_server_port', 'email_smtp_server_username', /*'email_smtp_server_password',*/ 'email_smtp_server_security'];
                    $email_error = false;
                    foreach ($email_validate as $k) {
                        if (!$validations[$k]['validate']) {
                            $email_error = true;
                        }
                    }
                    $valid_mail_credentials = false;
                    if (!$email_error) {
                        try {
                            $mail = new Mailer(true);
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = $POST['email_smtp_server_security'];
                            $mail->SMTPAutoTLS = in_array($POST['email_smtp_server_security'], ['ssl', 'tls']);
                            $mail->Username = $POST['email_smtp_server_username'];
                            $mail->Password = $POST['email_smtp_server_password'];
                            $mail->Host = $POST['email_smtp_server'];
                            $mail->Port = $POST['email_smtp_server_port'];
                            if (in_array(Config::system()->debugLevel(), [2, 3])) {
                                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                                $GLOBALS['SMTPDebug'] = '';
                                $mail->Debugoutput = function ($str) {
                                    $GLOBALS['SMTPDebug'] .= "$str\n";
                                };
                                $GLOBALS['SMTPDebug'] = "SMTP Debug>>\n" . $GLOBALS['SMTPDebug'];
                            }
                            $valid_mail_credentials = $mail->SmtpConnect();
                        } catch (Exception $e) {
                            $GLOBALS['SMTPDebug'] = "SMTP Exception>>\n" . ($mail->ErrorInfo ?: $e->getMessage());
                        }
                        if (!$valid_mail_credentials) {
                            foreach (array_keys($email_smtp_validate) as $k) {
                                $validations[$k]['validate'] = false;
                            }
                        }
                    }
                }

                if (isset($POST['akismet']) && $POST['akismet'] == 1 && $validations['akismet_api_key']['validate']) {
                    $akismet = new Akismet($POST['akismet_api_key']);

                    try {
                        $verifyAkismet = $akismet->verifyKey();
                    } catch (Throwable $e) {
                        $verifyAkismet = false;
                    }
                    $validations['akismet_api_key'] = [
                        'validate' => $verifyAkismet,
                        'error_msg' => _s('Invalid key')
                    ];
                }

                if (isset($POST['cdn']) && $POST['cdn'] == 1) {
                    $cdn_url = trim($POST['cdn_url'], '/') . '/';
                    if (!is_url($cdn_url)) {
                        $cdn_url = 'http://' . $cdn_url;
                    }
                    if (!is_url($cdn_url) && !is_valid_url($cdn_url)) {
                        $validations['cdn_url'] = [
                            'validate' => false,
                            'error_msg' => _s('Invalid URL')
                        ];
                    } else {
                        $POST['cdn_url'] = $cdn_url;
                        $handler::updateVar('safe_post', ['cdn_url' => $cdn_url]);
                    }
                }

                if (isset($POST['captcha']) && $POST['captcha'] == 1) {
                    foreach (['captcha_sitekey', 'captcha_secret'] as $v) {
                        $validations[$v] = ['validate' => (bool) $POST[$v]];
                    }
                }

                foreach (array_keys($POST + files()) as $k) {
                    if (isset($validations[$k]) && !$validations[$k]['validate']) {
                        $input_errors[$k] = $validations[$k]['error_msg'] ?? _s('Invalid value');
                    }
                }

                if (isset($POST[$route]) && $POST[$route] == 'pages' && in_array($handler->request()[2], ['edit', 'add']) && $POST['page_type'] == 'internal') {
                    if ($page ?? false) {
                        $try_page_db = $page['url_key'] !== $POST['url_key'];
                        if (!$try_page_db) {
                            $page['file_path'] !== $POST['page_file_path'];
                        }
                    } else {
                        $try_page_db = true;
                    }
                    if ($try_page_db) {
                        $db = DB::getInstance();
                        $db->query('SELECT * FROM ' . DB::getTable('pages') . ' WHERE page_url_key = :page_url_key OR page_file_path = :page_file_path');
                        $db->bind(':page_url_key', $POST['page_url_key']);
                        $db->bind(':page_file_path', $POST['page_file_path']);
                        $page_fetch_db = $db->fetchAll();
                        if ($page_fetch_db) {
                            foreach ($page_fetch_db as $k => $v) {
                                foreach ([
                                    'page_url_key' => _s('This URL key is already being used by another page (ID %s)'),
                                    'page_file_path' => _s('This file path is already being used by another page (ID %s)')
                                ] as $kk => $vv) {
                                    if (isset($page['id']) && $page['id'] == $v['page_id']) {
                                        continue; // Skip on same thing
                                    }
                                    if (hash_equals(
                                        (string) $v[$kk],
                                        (string) $POST[$kk]
                                    )) {
                                        $input_errors[$kk] = sprintf($vv, $v['page_id']);
                                    }
                                }
                            }
                        }
                    }
                }
                if (is_array($input_errors) && count($input_errors) == 0) {
                    if (isset($handler->request()[1]) && $handler->request()[1] == 'pages') {
                        if (in_array($handler->request()[2], ['edit', 'add']) && $POST['page_type'] == 'internal') {
                            $page_write_code = (array_key_exists('page_code', $POST)) ? (empty($POST['page_code']) ? null : html_entity_decode($POST['page_code'])) : null;

                            try {
                                Page::writePage(['file_path' => $POST['page_file_path'], 'code' => $page_write_code]);
                                if ($handler->request()[2] == 'edit'
                                    && isset($page['file_path'])
                                    && !hash_equals((string) $page['file_path'], (string) $POST['page_file_path'])
                                ) {
                                    unlinkIfExists(Page::getPath($page['file_path']));
                                }
                                if (isset($page['id'])) {
                                    Page::update((int) $page['id'], ['code' => $page_write_code]);
                                }
                            } catch (Exception $e) {
                                $input_errors['page_code'] = _s("Can't save page contents: %s.", $e->getMessage());
                            }
                        }
                        if (isset($POST['page_internal']) && $POST['page_internal'] == '') {
                            $POST['page_internal'] = null;
                        }
                        $page_fields = Page::getFields();
                        $page_values = [];
                        foreach ($page_fields as $v) {
                            $postPage = $POST['page_' . $v];
                            if ($handler->request()[2] == 'edit') {
                                if (hash_equals(
                                    (string) ($page[$v] ?? ''),
                                    (string) ($postPage ?? '')
                                )) {
                                    continue;
                                } // Skip not updated values
                            }
                            $page_values[$v] = $postPage;
                        }
                        if ($page_values !== []) {
                            if ($handler->request()[2] == 'add') {
                                $page_inserted = Page::insert($page_values);
                                sessionVar()->put('dashboard_page_added', ['id' => $page_inserted]);
                                redirect('dashboard/settings/pages/edit/' . $page_inserted);
                            } else {
                                if (isset($page['id'])) {
                                    Page::update((int) $page['id'], $page_values);
                                }
                                $is_changed = true;
                                $pages_sort_changed = false;
                                foreach (['sort_display', 'is_active', 'is_link_visible'] as $k) {
                                    if (isset($page[$k], $page_values[$k]) && $page[$k] !== $page_values[$k]) {
                                        $pages_sort_changed = true;

                                        break;
                                    }
                                }
                                // Update 'page' var
                                $page = array_merge($handler::var('page'), $page_values);
                                Page::fill($page);
                                $handler::updateVar('page', $page);
                                // Update pages_link_visible (menu)
                                $pages_link_visible = $handler::var('pages_link_visible');
                                $pages_link_visible[$page['id']] = $page; // Either update or append
                                if (!$page['is_active'] || !$page['is_link_visible']) {
                                    unset($pages_link_visible[$page['id']]);
                                } elseif ($pages_sort_changed) { // Need to update the sort display?
                                    uasort($pages_link_visible, function ($a, $b) {
                                        return $a['sort_display'] - $b['sort_display'];
                                    });
                                }
                                $handler::setVar('pages_link_visible', $pages_link_visible);
                            }
                        }
                    } else { // Settings
                        $update_settings = [];
                        foreach (array_keys(getSettings()) as $k) {
                            if (isset($POST[$k]) && $POST[$k] != (is_bool(getSetting($k)) ? (getSetting($k) ? 1 : 0) : getSetting($k))) {
                                $update_settings[$k] = $POST[$k];
                            }
                        }
                        if ($update_settings !== [] && Settings::update($update_settings)) {
                            $oldSettings = Settings::get();
                            new Settings();
                            $diffSettings = array_diff_key($oldSettings, Settings::get());
                            foreach ($diffSettings as $k => $v) {
                                Settings::setValue($k, $v);
                            }
                            $is_changed = true;
                            $reset_notices = false;
                            $settings_to_vars = [
                                'website_doctitle' => 'doctitle',
                                'website_description' => 'meta_description',
                                'theme_font' => 'theme_font'
                            ];
                            foreach (array_keys($update_settings) as $k) {
                                if ($k == 'maintenance') {
                                    $reset_notices = true;
                                }
                                if (array_key_exists($k, $settings_to_vars)) {
                                    $handler::setVar($settings_to_vars[$k], getSetting($k));
                                }
                            }
                            if ($reset_notices) {
                                $system_notices = getSystemNotices();
                                $handler::setVar('system_notices', $system_notices);
                            }
                        }
                    }
                } else {
                    $is_error = true;
                }
            }

            break;

        case 'images':
        case 'albums':
        case 'users':
            $getParams = Listing::getParams(request());
            $tabs = Listing::getTabs([
                'listing' => $doing,
                'tools' => true,
            ], $getParams);
            $type = $doing;
            $current = false;
            foreach ($tabs as $k => $v) {
                if ($v['current']) {
                    $current = $k;
                }
                $tabs[$k]['type'] = $type;
                $tabs[$k]['url'] = get_base_url('dashboard/' . $type . '/?' . $tabs[$k]['params']);
            }
            if (!$current) {
                $current = 0;
                $tabs[0]['current'] = true;
            }
            $handler::setVar('list_params', $getParams);
            parse_str($tabs[$current]['params'], $tab_params);
            preg_match(
                '/(.*)\_(asc|desc)/',
                empty(request()['sort'])
                    ? $tab_params['sort']
                    : request()['sort'],
                $sort_matches
            );
            $getParams['sort'] = array_slice($sort_matches, 1);
            $listing = new Listing();
            $listing->setType($type); // images | users | albums
            if (isset($getParams['reverse'])) {
                $listing->setReverse($getParams['reverse']);
            }
            if (isset($getParams['seek'])) {
                $listing->setSeek($getParams['seek']);
            }
            $listing->setOffset($getParams['offset']);
            $listing->setLimit($getParams['limit']); // how many results?
            $listing->setSortType($getParams['sort'][0]); // date | size | views
            $listing->setSortOrder($getParams['sort'][1]); // asc | desc
            $listing->setRequester($logged_user);
            $listing->setOutputTpl($type);
            $listing->exec();

            break;
    }
    if ($doing != 'stats') {
        $pre_doctitle[] = $routesLinkLabels[$doing];
        if ($doing == 'settings' && isset($settings_sections)) {
            reset($settings_sections);
            $firstKey = key($settings_sections);
            $dashSettingsProp = $handler::var('settings');
            if ($dashSettingsProp['key'] != $firstKey) {
                $pre_doctitle[] = $dashSettingsProp['title'];
            }
        }
    }
    $pre_doctitle[] = _s('Dashboard');
    $handler::setVar('pre_doctitle', implode(' - ', $pre_doctitle));
    $handler::setCond('error', $is_error);
    $handler::setCond('changed', $is_changed);
    $handler::setVar('error_message', $error_message);
    $handler::setVar('input_errors', $input_errors);
    $handler::setVar('changed_message', $changed_message ?? null);
    if (isset($tabs)) {
        $handler::setVar('sub_tabs', $tabs);
    }
    if (isset($listing)) {
        $handler::setVar('listing', $listing);
    }
    $handler::setVar('share_links_array', get_share_links());
    $handler::setVar('paid_settings', $paidSettings);
};
