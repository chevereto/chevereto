<?php

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_current_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\is_route;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_last;
use function Chevereto\Legacy\get_html_tags;
use function Chevereto\Legacy\get_static_url;
use function Chevereto\Legacy\get_system_image_url;
use function Chevereto\Legacy\get_theme_file_url;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\include_peafowl_head;
use function Chevereto\Legacy\show_theme_inline_code;
use function Chevereto\Legacy\theme_file_exists;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<!DOCTYPE HTML>
<html <?php echo get_html_tags(); ?> prefix="og: http://ogp.me/ns#">
<head>
    <?php include_theme_file('custom_hooks/head_open'); ?>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (Handler::var('safe_html_meta_description')) { ?>
    <meta name="description" content="<?php echo Handler::var('safe_html_meta_description'); ?>">
<?php
}   if (Handler::var('canonical') !== null && Handler::var('canonical') != get_current_url(true, ['lang'])) {
    ?>
    <link rel="canonical" href="<?php echo Handler::var('canonical'); ?>">
<?php
} ?>
    <title><?php echo Handler::var('safe_html_doctitle'); ?></title>
    <?php include_peafowl_head(); ?>
    <link rel="shortcut icon" href="<?php echo get_system_image_url(getSetting('favicon_image')); ?>">
    <link rel="icon" type="image/png" href="<?php echo get_system_image_url(getSetting('favicon_image')); ?>" sizes="192x192">
    <link rel="apple-touch-icon" href="<?php echo get_system_image_url(getSetting('favicon_image')); ?>" sizes="180x180">
    <?php if (is_route('image') && Handler::var('image') !== null && Handler::var('image')['is_360']) { ?>
    <link rel="stylesheet" href="<?php echo get_static_url(PATH_PUBLIC_CONTENT_LEGACY_THEMES_PEAFOWL_LIB . 'js/pannellum.css'); ?>">
    <script type="text/javascript" src="<?php echo get_static_url(PATH_PUBLIC_CONTENT_LEGACY_THEMES_PEAFOWL_LIB . 'js/pannellum.js'); ?>"></script>
    <?php } ?>
    <?php
    if (!Handler::cond('maintenance')) {
        include_theme_file('snippets/embed');
    }
    if (getSetting('theme_logo_height') !== null) {
        $logo_height = getSetting('theme_logo_height');
        echo '<style type="text/css">.top-bar-logo img { height: ' . intval(getSetting('theme_logo_height')) . 'px; }</style>';
    }
    $open_graph = [
        'type' => 'website',
        'url' => get_current_url(true, ['lang']),
        'title' => safe_html(getSetting('website_doctitle')),
        'image' => getSetting('homepage_cover_images')[0]['url'] ?? '',
        'site_name' => Handler::var('safe_html_website_name'),
        'description' => Handler::var('safe_html_meta_description'),
    ];
    if (getSetting('facebook_app_id')) {
        $open_graph['fb:app_id'] = getSetting('facebook_app_id');
    }
    switch (true) {
        case Handler::var('image') !== null and is_route('image'):
            $open_graph_extend = [
                'type' => 'article',
                'title' => Handler::var('pre_doctitle'),
                // 'description'	=> _var('image')['description'],
                'image' => Handler::var('image')['url'],
                'image:width' => Handler::var('image')['width'],
                'image:height' => Handler::var('image')['height'],
            ];
            if (Handler::var('image')['is_animated'] && Handler::var('image')['size'] < get_bytes('8 MiB')) {
                $open_graph_extend['type'] = 'video.other';
                $open_graph_extend['url'] = Handler::var('image')['url'];
            }

            break;
        case Handler::var('album') !== null and is_route('album'):
            $open_graph_extend = [
                'type' => 'article',
                'title' => Handler::var('pre_doctitle'),
                // 'description'	=> _var('album')['description'] ?: _var('album')['name'],
            ];
            if (in_array(Handler::var('album')['privacy'], ['public', 'private_but_link']) && Handler::var('listing')->outputCount()) {
                $open_graph_extend = array_merge($open_graph_extend, [
                    'image' => Handler::var('listing')->outputAssoc()[0]['display_url'],
                    'image:width' => Handler::var('listing')->outputAssoc()[0]['display_width'],
                    'image:height' => Handler::var('listing')->outputAssoc()[0]['display_height'],
                ]);
            }

            break;
        case Handler::var('user') !== null and is_route('user'):
            $open_graph_extend = [
                'type' => 'profile',
                'title' => Handler::var('user')['name'],
                // 'description'	=> sprintf(is_user_images() ? _s("%s's Images") : _s("%s's Albums"), _var('user')["name_short"]),
                'image' => isset(Handler::var('user')['avatar']) ? Handler::var('user')['avatar']['url'] : '',
            ];

            break;
        case Handler::var('album') !== null and is_route('album'):
            $open_graph_extend = [
                'title' => Handler::var('album')['name'],
                // 'description'	=> _var('album')['description'],
            ];

            break;
    }
    if (isset($open_graph_extend)) {
        $open_graph = array_merge($open_graph, $open_graph_extend);
    }
    foreach ($open_graph  as $k => $v) {
        if (!$v) {
            continue;
        }
        $prop = strpos($k, ':') !== false ? $k : "og:$k";
        echo '<meta property="' . $prop . '" content="' . safe_html($v, ENT_COMPAT) . '" />' . "\n";
    }
    $twitter_card = [
        'card' => 'summary',
        'description' => Handler::var('safe_html_meta_description'),
        'title' => str_replace_last(' - ' . Handler::var('safe_html_website_name'), '', Handler::var('safe_html_doctitle')),
        'site' => getSetting('twitter_account') ? ('@' . getSetting('twitter_account')) : null,
    ];
    switch (true) {
        case is_route('image'):
            $twitter_card['card'] = 'photo';

            break;
        case Handler::var('admin') !== null and is_route('album'):
        case Handler::var('user') !== null and is_route('user'):
            $twitter_card['card'] = 'gallery';
            if (is_route('album')) {
                $twitter_card['creator'] = Handler::var('album')['user']['twitter']['username'];
            } else {
                $twitter_card['creator'] = isset(Handler::var('user')['twitter']) ? Handler::var('user')['twitter']['username'] : '';
            }
            $list_output = Handler::var('listing') !== null ? (Handler::var('listing')->outputAssoc() ?? null) : null;
            if (is_array($list_output) && count($list_output) > 0) {
                for ($i = 0; $i < 4; ++$i) {
                    $twitter_card['image' . $i] = $list_output[$i]['display_url'] ?? '';
                }
            }

            break;
    }
    foreach ($twitter_card as $k => $v) {
        if (!$v) {
            continue;
        }
        echo '<meta name="twitter:' . $k . '" content="' . $v . '">' . "\n";
    }
    if (Handler::var('oembed')) {
        foreach (['json', 'xml'] as $format) {
            echo '    <link rel="alternate" type="application/' . $format . '+oembed" href="'
            . get_base_url('oembed/?url=' . urlencode(Handler::var('oembed')['url']) . '&format=' . $format)
            . '" title="' . Handler::var('oembed')['title'] . '">' . "\n";
        }
    }
    if (Handler::var('image') !== null and is_route('image')) { ?>
    <link rel="image_src" href="<?php echo Handler::var('image')['url']; ?>">
            <?php
    }
            if (getSetting('theme_custom_css_code')) {
                ?>
                <style>
                    <?php echo getSetting('theme_custom_css_code'); ?>
                </style>
            <?php
            }
            if (getSetting('theme_custom_js_code')) {
                ?>
                <script>
                    <?php echo getSetting('theme_custom_js_code'); ?>
                </script>
            <?php
            }
            show_theme_inline_code('snippets/theme_colors.css');
            if (theme_file_exists('custom_hooks/style.css')) {
                ?>
                <link rel="stylesheet" href="<?php echo get_theme_file_url('custom_hooks/style.css'); ?>">
            <?php
            }
            ?>
            <link rel="alternate" hreflang="x-default" href="<?php echo get_current_url(true, ['lang']); ?>">
            <?php
            foreach (Handler::var('langLinks') as $k => $v) {
                echo '<link rel="alternate" hreflang="' . $v['hreflang'] . '" href="' . $v['url'] . '">' . "\n";
            }
            include_theme_file('custom_hooks/head'); ?>
</head>
<?php include_theme_file('custom_hooks/head_after'); ?>
