<?php

use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Palettes;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\G\get_route_path;
use function Chevereto\Legacy\G\get_template_used;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\is_prevented_route;
use function Chevereto\Legacy\G\is_route;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\get_language_used;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getThemeLogo;
use function Chevereto\Legacy\linkPaid;
use function Chevereto\Vars\env;

require_theme_file('head');
try {
    require_theme_file('custom_hooks/header');
} catch (Throwable $e) {
}
$body_class = '';
if (!is_prevented_route()
    && in_array(get_template_used(), ['user', 'image']) && !Handler::cond('404')
) {
    $body_class = (
        is_route('image')
            || (is_route('user') && isset(Handler::var('user')['background']))
            || Handler::cond('owner')
            || Handler::cond('content_manager')
    )
        ? ' no-margin-top'
        : '';
}
$templateUsed = Handler::getTemplateUsed();
if (Handler::cond('maintenance') || Handler::cond('show_consent_screen')
    || in_array($templateUsed, ['request-denied', '404'])
) {
    $body_class = '';
} else {
    if (get_route_name() == 'index') {
        $body_class = getSetting('homepage_style');
        if (Handler::var('list') !== null) {
            $listing = Handler::var('listing');
            $hasPrev = $listing->has_page_prev();
            if ($hasPrev) {
                $body_class = '';
            }
        }
    }
}
if (is_route('page') || is_route('plugin')) {
    $body_class = 'page';
}
?>
<body id="<?php echo $templateUsed; ?>" class="<?php echo $body_class; ?>" data-route="<?php echo get_route_path(true); ?>">
<?php
try {
    require_theme_file('custom_hooks/body_open');
} catch (Throwable $e) {
}
if (Handler::cond('show_viewer_zero')) { ?>
    <div class="viewer viewer--zero"></div>
<?php } ?>
<?php
if (Handler::cond('show_header')) {
?>
    <header id="top-bar" class="top-bar top-bar--main">
        <div class="content-width">
            <div id="logo" class="top-bar-logo no-select">
                <a href="<?php echo Handler::var('header_logo_link'); ?>"><?php echo getThemeLogo(); ?></a>
            </div>
<?php
                if (getSetting('website_privacy_mode') == 'public'
                    || (getSetting('website_privacy_mode') == 'private' && Login::isLoggedUser())) { ?>
                <ul class="top-bar-left float-left">
                    <li data-action="top-bar-menu-full" data-nav="mobile-menu" class="top-btn-el phone-show hidden">
                        <span class="top-btn-text"><span class="icon fas fa-bars"></span></span>
                    </li>
                    <?php if (Handler::cond('explore_enabled')) { ?>
                        <li id="top-bar-explore" data-nav="explore" class="phone-hide-- menu-hide pop-keep-click pop-btn pop-btn-show<?php if (in_array(get_route_name(), ['explore'])) {
                        ?> current<?php
                    } ?>">
                            <?php
                                        $cols = 1;
                                        $categories = Handler::var('categories');
                                        if (count($categories) > 0) {
                                            array_unshift($categories, [
                                                'id' => null,
                                                'name' => _s('All'),
                                                'url_key' => null,
                                                'url' => get_base_url('explore'),
                                            ]);
                                            $cols = min(5, round(count($categories) / 5, 0, PHP_ROUND_HALF_UP));
                                        }
                                        ?>
                            <span class="top-btn-text"><span class="icon fas fa-compass"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Explore'); ?></span></span>
                            <div class="pop-box --auto-cols <?php if ($cols > 1) {
                                            echo sprintf('pbcols%d ', $cols);
                                        } ?>arrow-box arrow-box-top anchor-left">

                                <div class="pop-box-inner pop-box-menu<?php if ($cols > 1) {
                                            ?> pop-box-menucols<?php
                                        } ?>">
<?php
if (Handler::var('explore_discovery') !== []) {
    $explore_discovery = Handler::var('explore_discovery');
?>
                                        <div class="pop-box-label"><?php _se('Discovery'); ?></div>
                                        <ul>
<?php
foreach ($explore_discovery as $k => $v) {
    echo '<li><a href="' . $v['url'] . '"><span class="btn-icon ' . $v['icon'] . '"></span><span class="btn-text">' . $v['label'] . '</span></a></li>';
}
?>
                                        </ul>
<?php } ?>
                                        <?php
if (Handler::var('explore_content') !== []) {
    $explore_content = Handler::var('explore_content');
?>
                                        <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                        <div class="pop-box-label"><?php _se('Content'); ?></div>
                                        <ul>
<?php
foreach ($explore_content as $k => $v) {
    echo '<li><a href="' . $v['url'] . '"><span class="btn-icon ' . $v['icon'] . '"></span><span class="btn-text">' . $v['label'] . '</span></a></li>';
}
?>
                                        </ul>
<?php } ?>
<?php
if (count($categories) > 0) {
?>                                      <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                        <div class="pop-box-label phone-margin-top-20"><?php _se('Categories'); ?></div>
                                        <ul>
<?php
foreach ($categories as $k => $v) {
    echo '<li data-content="category" data-category-id="'
        . $v['id']
        . '"><a data-content="category-name" data-link="category-url" href="'
        . $v['url']
        . '">'
        . safe_html($v['name'])
        . '</a></li>'
        . "\n";
}
?>
                                        </ul>
<?php } ?>
<?php
$tags_top = Handler::var('tags_top') ?? [];
if($tags_top !== []) {
?>
                                    <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                    <div class="pop-box-label phone-margin-top-20"><?php _se('Top %s', _n('Tag', 'Tags', 20)); ?></div>
                                    <div class="pop-box-block margin-top-5 margin-bottom-5">
<?php

foreach($tags_top as $k => $v) {
    echo '<a class="tag--pop-box" href="'
        . $v['url']
        . '">'
        . $v['name_safe_html']
        // . ' (F' . $v['files'] . ' V' . $v['views'] . ')'
        . '</a>'
        . "\n";
}
?>

                                    </div>
<?php } ?>
                                </div>
                            </div>
                        </li>
                    <?php
                            } ?>

                    <?php if (Handler::cond('random_enabled')) {
                                ?>
                        <li id="top-bar-random" data-nav="random" class="top-btn-el phone-hide">
                            <a aria-label="<?php _se('Random'); ?>" href="<?php echo get_base_url('?random'); ?>"><span class="top-btn-text"><span class="icon fas fa-random"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Random'); ?></span></span></a>
                        </li>
                    <?php
                            } ?>

                    <?php if (Handler::cond('search_enabled')) {
                                ?>
                        <li data-action="top-bar-search" data-nav="search" class="phone-hide pop-btn">
                            <span class="top-btn-text"><span class="icon fas fa-search"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Search'); ?></span></span>
                        </li>
                        <li data-action="top-bar-search-input" class="top-bar-search-input phone-hide pop-btn pop-keep-click hidden">
                            <div class="input-search">
                                <form action="<?php echo get_base_url('search/images'); ?>/" method="get">
                                    <input required class="search" type="text" placeholder="<?php _se('Search'); ?>" autocomplete="off" spellcheck="false" name="q">
                                </form>
                                <span class="fas fa-search icon--search"></span><span class="icon--close fas fa-times" data-action="clear-search" title="<?php _se('Close'); ?>"></span><span class="icon--settings fa-solid fa-sliders" data-modal="form" data-target="advanced-search" title="<?php _se('Advanced search'); ?>"></span>
                            </div>
                        </li>
                        <div class="hidden" data-modal="advanced-search">
                            <span class="modal-box-title"><i class="fa-solid fa-sliders margin-right-5"></i><?php _se('Advanced search'); ?></span>
                            <?php require_theme_file('snippets/form_advanced_search'); ?>
                        </div>
                    <?php
                            } ?>
                </ul>
            <?php
    } ?>
            <ul class="top-bar-right float-right keep-visible">

                <?php if (Handler::var('system_notices') !== []) {
        ?>
                    <li data-nav="notices" class="phone-hide top-btn-el" data-modal="simple" data-target="modal-notices">
                        <span class="top-btn-text"><span class="icon fas fa-exclamation-triangle color-fail"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Notices (%s)', count(Handler::var('system_notices'))); ?></span></span>
                    </li>
                <?php
    } ?>
            <?php if (Handler::cond('upload_enabled')) {
        ?>
                    <li data-action="top-bar-upload" data-link="<?php echo getSetting('upload_gui'); ?>" data-nav="upload" class="<?php if (is_route('upload')) {
            echo 'current ';
        } ?>top-btn-el" <?php if (!getSetting('guest_uploads')) {
            ?> data-login-needed="true" <?php
        } ?>>
                        <a aria-label="<?php _se('Upload'); ?>" href="<?php echo get_base_url('upload'); ?>" class="top-btn-text"><span class="icon fas fa-cloud-upload-alt"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Upload'); ?></span></a>
                    </li>
                <?php
    } ?>

                <?php
                    if (!Login::isLoggedUser()) {
                        ?>
                    <li id="top-bar-signin" data-nav="signin" class="<?php if (is_route('login')) {
                            echo 'current ';
                        } ?>top-btn-el">
                        <a aria-label="<?php _se('Sign in'); ?>" href="<?php echo get_base_url('login'); ?>" class="top-btn-text"><span class="icon fas fa-sign-in-alt"></span><span class="btn-text phone-hide phablet-hide"><?php _se('Sign in'); ?></span>
                        </a>
                    </li>
                    <?php
                    } else {
                        if (Handler::cond('show_notifications')) {
                            $notifications_unread = Login::getUser()['notifications_unread'];
                            $notifications_display = Login::getUser()['notifications_unread_display'];
                            $notifications_counter = strtr('<span data-content="notifications-counter" class="top-btn-number%c">' . $notifications_display . '</span>', ['%c' => $notifications_unread > 0 ? ' on' : '']); ?>
                        <li data-action="top-bar-notifications" class="top-bar-notifications phone-hide pop-btn pop-keep-click">
                            <div class="top-btn-text position-relative">
                                <div class="soft-hidden menu-fullscreen-show"><i class="icon fas fa-bell"></i><?php echo $notifications_counter; ?><span class="btn-text"><?php _se('Notifications'); ?></span></div>
                                <div class="menu-fullscreen-hide"><span class="icon fas fa-bell"></span><?php echo $notifications_counter; ?></div>
                            </div>
                            <div class="top-bar-notifications-container c9 pop-box arrow-box arrow-box-top anchor-center">
                                <div class="pop-box-inner">
                                    <div class="top-bar-notifications-header phone-hide phablet-hide">
                                        <span class="pop-box-label margin-0"><i class="fas fa-bell"></i> <?php _se('Notifications'); ?></span>
                                    </div>
                                    <div class="top-bar-notifications-list antiscroll-wrap hidden">
                                        <ul class="antiscroll-inner r8 overflow-scroll overflow-x-hidden touch-scroll"></ul>
                                    </div>
                                    <div class="loading text-align-center margin-top-20 margin-bottom-20 hidden">
                                        <div class="loading-indicator"></div>
                                        <div class="loading-text"><?php _se('loading'); ?></div>
                                    </div>
                                    <div class="empty text-align-center margin-top-20 margin-bottom-20 hidden">
                                        <?php _se("You don't have notifications"); ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php
                        } ?>
                    <li id="top-bar-user" data-nav="user" class="pop-btn pop-keep-click">
                        <span class="top-btn-text">
                            <?php if (isset(Login::getUser()['avatar'], Login::getUser()['avatar']['url'])) {
                            ?>
                                <img src="<?php echo Login::getUser()['avatar']['url']; ?>" alt="" class="user-image">
                            <?php
                        } else {
                            ?>
                                <img src="" alt="" class="user-image hidden">
                            <?php
                        } ?>
                            <span class="user-image default-user-image<?php echo isset(Login::getUser()['avatar']['url']) ? ' hidden' : ''; ?>"><span class="icon fas fa-user-circle"></span></span>
                            <span class="btn-text phone-hide phablet-hide">@<?php echo Login::getUser()['username']; ?></span>
                        </span>
                        <div class="pop-box arrow-box arrow-box-top anchor-right">
                            <div class="pop-box-inner pop-box-menu">
                                <ul>
                                    <li class="with-icon"><a href="<?php echo Login::getUser()['url']; ?>"><span class="btn-icon fas fa-id-card"></span><?php _se('My Profile'); ?></a></li>
                                    <?php
                                    if (Login::getUser()['home'] === 'files') {
                                        ?>
                                    <li class="with-icon"><a href="<?php echo Login::getUser()['url_albums']; ?>"><span class="btn-icon fas fa-images"></span><?php _ne('Album', 'Albums', 20); ?></a></li>
                                    <?php
                                    } else { ?>
                                    <li class="with-icon"><a href="<?php echo Login::getUser()['url_images']; ?>"><span class="btn-icon fas fa-photo-film"></span><?php _ne('File', 'Files', 20); ?></a></li>
                                    <?php } ?>
                                    <?php if (getSetting('enable_likes')) {
                                        ?>
                                        <li class="with-icon"><a href="<?php echo Login::getUser()['url_liked']; ?>"><span class="btn-icon fas fa-heart"></span><?php echo _s('Liked'); ?></a></li>
                                    <?php
                                    } ?>
                                        <li class="with-icon"><a href="<?php echo get_base_url('settings'); ?>"><span class="btn-icon fas fa-user-cog"></span><?php echo _s('Settings'); ?></a></li>
                                </ul>
                                <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                <div class="pop-box-label"><?php _se('Palette'); ?></div>
                                <div data-action="top-bar-tone">
                                    <div class="pop-box-block pop-keep-click phone-padding-0" data-content="palettes">
<?php
                        /** @var Palettes $palettes */
                        $palettes = Handler::var('palettes');
                        foreach (array_keys($palettes->get()) as $id) {
                            echo strtr('<a class="%class" data-action="palette" data-palette="%handle" data-id="%id">%name</a>', [
                                    '%class' => $id == Login::getUser()['palette_id'] ? 'current' : '',
                                    '%handle' => $palettes->getHandle($id),
                                    '%id' => (string) $id,
                                    '%name' => $palettes->getName($id),
                                ]);
                        } ?>
                                    </div>
                                </div>
<?php if (Handler::cond('content_manager')) { ?>
                                <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                <div class="pop-box-label"><?php Handler::cond('admin') ? _se('Administrator') : _se('Manager') ?></div>
                                <ul>
<?php if (Handler::cond('show_content_manager') && env()['CHEVERETO_EDITION'] !== 'free') { ?>
                                    <li class="with-icon">
                                        <a href="<?php echo Handler::var('moderate_link'); ?>"><span class="btn-icon fas fa-check-double"></span><?php echo Handler::var('moderate_label'); ?></a>
                                    </li>
<?php } ?>
<?php if (Handler::cond('admin')) { ?>
                                    <li class="with-icon"><a href="<?php echo get_base_url('dashboard'); ?>"><span class="btn-icon fas fa-tachometer-alt"></span><?php _se('Dashboard'); ?></a></li>
                                    <li class="with-icon"><a href="<?php echo get_base_url('dashboard/settings'); ?>"><span class="btn-icon fas fa-cog"></span><?php _se('Settings'); ?></a></li>
<?php } ?>
                                </ul>
<?php } ?>
                                <div class="or-separator margin-top-5 margin-bottom-5"></div>
                                <ul>
                                    <li class="with-icon">
                                    <form id="form-logout" action="<?php echo get_base_url('logout'); ?>" method="post" class="display-inline">
                                        <input type="hidden" name="auth_token" value="<?php echo Handler::var('auth_token'); ?>">
                                    </form>
                                        <a data-action="logout"><span class="btn-icon fas fa-sign-out-alt"></span><?php _se('Sign out'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                <?php
                    } ?>
                <?php
            if (!Login::isLoggedUser() and getSetting('language_chooser_enable')) {
                ?>
        <li data-nav="language" class="phone-hide pop-btn">
            <?php
                $langLinks = Handler::var('langLinks');
                $cols = min(5, ceil(count($langLinks) / 6)); ?>
            <span class="top-btn-text">
                <span class="icon fas fa-language"></span><span class="btn-text phablet-hide"><?php echo get_language_used()['short_name']; ?></span>
            </span>
            <div class="pop-box --auto-cols <?php if ($cols > 1) {
                    echo sprintf('pbcols%d ', $cols);
                } ?>arrow-box arrow-box-top anchor-center">
                <div class="pop-box-inner pop-box-menu<?php if ($cols > 1) {
                    ?> pop-box-menucols<?php
                } ?>">
                    <ul>
                        <?php
                                    foreach ($langLinks as $k => $v) {
                                        if($k === 'x-default') {
                                            continue;
                                        }
                                        echo '<li' . (get_language_used()['code'] == $k ? ' class="current"' : '') . '><a href="' . $v['url'] . '">' . $v['name'] . '</a></li>' . "\n";
                                    } ?>
                    </ul>
                </div>
            </div>
        </li>
    <?php
            } ?>
                <?php
                    if (getSetting('website_privacy_mode') == 'public' || (getSetting('website_privacy_mode') == 'private' && Login::isLoggedUser())) {
                        ?>
                    <?php
                            if (Handler::var('pages_link_visible')) {
                                ?>
                        <li data-nav="about" class="phone-hide pop-btn pop-keep-click">
                            <span class="top-btn-text">
                                <span class="icon far fa-question-circle"></span><span class="btn-text phone-hide phablet-hide laptop-hide tablet-hide desktop-hide"><?php _se('About'); ?></span>
                            </span>
                            <div class="pop-box arrow-box arrow-box-top anchor-right">
                                <div class="pop-box-inner pop-box-menu">
                                    <ul>
                                        <?php
                                                    foreach (Handler::var('pages_link_visible') as $page) {
                                                        ?>
                                            <li<?php if ($page['icon']) {
                                                            echo ' class="with-icon"';
                                                        } ?>><a <?php echo $page['link_attr']; ?>><?php echo $page['title_html']; ?></a>
                                            </li>
                    <?php
                                                    } ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
    <?php
                            } ?>
<?php
                    } ?>
            </ul>
        </div>
    </header>
    <?php if (Handler::var('system_notices') !== []) { ?>
    <div id="modal-notices" class="hidden">
        <span class="modal-box-title"><i class="fas fa-exclamation-triangle"></i> <?php _se('Notices (%s)', count(Handler::var('system_notices'))); ?></span>
        <ul class="list-style-type-decimal list-style-position-inside">
        <?php foreach (Handler::var('system_notices') as $notice) { ?>
            <li class="margin-top-10 margin-bottom-10"><?php echo $notice; ?></li>
        <?php } ?>
        </ul>
    </div>
    <?php } ?>
<?php
}
?>
