<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Album;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\IpBan;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_current_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\html_to_bbcode;
use function Chevereto\Legacy\G\is_animated_image;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getIdFromURLComponent;
use function Chevereto\Legacy\getIpButtonsArray;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\isShowEmbedContent;
use function Chevereto\Legacy\redirectIfRouting;
use function Chevereto\Vars\env;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    redirectIfRouting('image', $handler->requestArray()[0]);
    $imageIndex = getSetting('root_route') === 'image'
        ? 0
        : 1;
    $request_handle = $imageIndex == 0
        ? $handler->requestArray()
        : $handler->request();
    if (($request_handle[0] ?? null) === null) {
        $handler->issueError(404);

        return;
    }
    $id = getIdFromURLComponent($request_handle[0]);
    if ($id == 0) {
        $handler->issueError(404);

        return;
    }
    if (!isset(session()['image_view_stock'])) {
        sessionVar()->put('image_view_stock', []);
    }

    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    $image = Image::getSingle($id, !in_array($id, session()['image_view_stock']), true, $logged_user);
    if ($image === [] || !isset($image['url'])) {
        $handler->issueError(404);

        return;
    }
    if (!(bool) env()['CHEVERETO_ENABLE_USERS']
        && ($image['user']['id'] ?? 'not-found') != getSetting('website_mode_personal_uid')) {
        $handler->issueError(404);

        return;
    }
    if ($handler->isRequestLevel(3)) {
        if ($request_handle[1] === 'delete') {
            $password = $request_handle[2] ?? '';
            if (Image::verifyPassword($id, $password)) {
                Image::delete($id);
                redirect($image['path_viewer'] . '?deleted');
            }
        }

        $handler->issueError(404);

        return;
    }
    if (!$image['is_approved'] && (!($logged_user['is_manager'] ?? false) && !($logged_user['is_admin'] ?? false))) {
        $handler->issueError(403);

        return;
    }
    if ($image['path_viewer'] != get_current_url(true, ['lang'])) {
        redirect($image['path_viewer']);
    }
    $handler::setVar('canonical', $image['url_viewer']);
    if ((!$handler::cond('content_manager')
            && ($image['user']['status'] ?? null) == 'banned')
        ) {
        $handler->issueError(404);

        return;
    }
    sessionVar()->put('last_viewed_image', encodeID((int) $id));
    if ($image['file_resource']['type'] == 'path' && (!$image['is_animated'] && isset($image['file_resource']['chain']['image']) && is_animated_image($image['file_resource']['chain']['image']))) {
        Image::update($id, ['is_animated' => 1]);
        $image['is_animated'] = 1;
    }

    $is_owner = isset($image['user']['id']) ? ($image['user']['id'] == ($logged_user['id'] ?? null)) : false;
    if (getSetting('website_privacy_mode') == 'private') {
        if ($handler::cond('forced_private_mode')) {
            $image['album']['privacy'] = getSetting('website_content_privacy_mode');
        }
        if (!Login::getUser() && ($image['album']['privacy'] ?? null) != 'private_but_link') {
            redirect('login');
        }
    }
    if (!$handler::cond('content_manager') && !$is_owner && ($image['album']['privacy'] ?? null) == 'password' && !Album::checkSessionPassword($image['album'])) {
        sessionVar()->put('redirect_password_to', $image['path_viewer']);
        redirect($image['album']['url']);
    }
    if (isset($image['user']['is_private'])
        && $image['user']['is_private'] == 1
        && !$handler::cond('content_manager')
        && $image['user']['id'] != ($logged_user['id'] ?? null)
    ) {
        unset($image['user']);
        $image['user'] = User::getPrivate();
    }
    if (!$handler::cond('content_manager') && in_array($image['album']['privacy'] ?? null, ['private', 'custom']) && !$is_owner) {
        $handler->issueError(404);

        return;
    }
    if (isset($image['user']['id'])) {
        $image['user']['albums'] = User::getAlbums((int) $image["user"]["id"]);
    }
    $is_album_cover = false;
    if (isset($image['album']['id'])) {
        $album = Album::getSingle((int) $image['album']['id']);
        $is_album_cover = ($album['cover_id'] ?? 0) == $image['id'];
        $get_album_slice = Image::getAlbumSlice((int) $image['id'], (int) $image['album']['id'], 2);
        $image_album_slice = array_merge($image['album'], $get_album_slice);
    }
    $handler::setCond('album_cover', $is_album_cover);
    $image_safe_html = safe_html($image);
    $image['alt'] = $image_safe_html['description'] ?? ($image_safe_html['title'] ?? $image_safe_html['name']);
    $pre_doctitle = isset($image['title'])
        ? strip_tags($image['title'])
        : $image_safe_html['name'] . '.' . $image['extension'] . ' hosted at ' . getSetting('website_name');
    $tabs = [];
    $tabs[] = [
        'icon' => 'fas fa-image',
        'label' => _s('About'),
        'id' => 'tab-about',
        'current' => true,
    ];
    if (isShowEmbedContent()) {
        $tabs[] = [
            'icon' => 'fas fa-code',
            'label' => _s('Embed codes'),
            'id' => 'tab-embeds',
        ];
    }
    if ($handler::cond('content_manager')) {
        if ($handler::cond('admin')) {
            $tabs[] = [
                'icon' => 'fas fa-info-circle',
                'label' => _s('Info'),
                'id' => 'tab-info',
            ];
        }
        $bannedIp = IpBan::getSingle(['ip' => $image['uploader_ip']]);
        $buttonSearchIp = '';
        $buttonBanIp = '';
        $ipBanNotice = '';
        if ((bool) env()['CHEVERETO_ENABLE_USERS']) {
            $buttonSearchIp = '<a class="btn btn-small default" href="' . get_base_url('search/images/?q=ip:%1$s') . '"><i class="fas fa-search"></i> ' . _s('Search') . '</a>';
        }
        if ((bool) env()['CHEVERETO_ENABLE_IP_BANS']) {
            $buttonBanIp = ($bannedIp === [] ? ('<a class="btn btn-small default" data-modal="form" data-args="%IP" data-target="modal-add-ip_ban" data-options=\'{"forced": true}\' data-content="ban_ip"><i class="fas fa-ban"></i> ' . _s('Ban') . '</a>') : '');
            $ipBanNotice = '<span class="btn btn-small default disabled' . ($bannedIp !== [] ? '' : ' hidden') . '" data-content="banned_ip"><i class="fas fa-ban"></i> ' . _s('Banned') . '</span>';
        }
        $image_admin_list_values = [
            [
                'label' => _s('Image ID'),
                'content' => $image['id'] . ' (' . $image['id_encoded'] . ')',
            ],
            getIpButtonsArray($bannedIp, $image['uploader_ip']),
            [
                'label' => _s('Upload date'),
                'content' => $image['date'],
            ],
            [
                'label' => '',
                'content' => $image['date_gmt'] . ' (GMT)',
            ],
        ];
        $handler::setVar('content_ip', $image['uploader_ip']);
        $handler::setVar('image_admin_list_values', $image_admin_list_values);
        $handler::setCond('banned_ip', $bannedIp !== []);
    }
    // tab-embeds, tab-about, tab-info
    $firstTabSetting = getSetting('image_first_tab');
    if (!$handler::cond('admin') && $firstTabSetting == 'info') {
        $firstTabSetting = 'embeds';
    }
    if (!isShowEmbedContent() && $firstTabSetting == 'embeds') {
        $firstTabSetting = 'about';
    }
    $firstTab = 'tab-' . $firstTabSetting;
    $currentTab = [];
    $currentTabId = '';
    if (count($tabs) === 1) {
        $tabs[0]['current'] = true;
        $currentTabId = $tabs[0]['id'];
    } else {
        foreach ($tabs as $k => &$v) {
            if ($v['id'] !== $firstTab) {
                $v['current'] = false;

                continue;
            }
            $currentKey = $k;
            $currentTabId = $v['id'];
            $v['current'] = true;
            $currentTab = $v;
        }
        if (isset($currentKey)) {
            unset($tabs[$currentKey]);
        }
        array_unshift($tabs, $currentTab);
    }
    $handler::setVar('current_tab', str_replace_first('tab-', '', $currentTabId));
    $handler::setCond('owner', $is_owner);
    $handler::setVar('pre_doctitle', $pre_doctitle);
    $handler::setVar('image', $image);
    $handler::setVar('image_safe_html', $image_safe_html);
    $handler::setVar('image_album_slice', safe_html($image_album_slice ?? []));
    $handler::setVar('tabs', $tabs);
    $handler::setVar('owner', $image['user'] ?? []);
    if (isset($image['description'])) {
        $meta_description = $image['description'];
    } else {
        $image_tr = [
            '%i' => $image[is_null($image['title']) ? 'filename' : 'title'],
            '%a' => $image['album']['name'] ?? '',
            '%w' => getSetting('website_name'),
            '%c' => $image['category']['name'] ?? '',
        ];
        if (isset($image['album']['id'])
            || (
                !((bool) ($image['user']['is_private'] ?? false)) && isset($image['album']['name'])
            )) {
            $meta_description = _s('Image %i in %a album', $image_tr);
        } elseif (isset($image['category']['id'])) {
            $meta_description = _s('Image %i in %c category', $image_tr);
        } else {
            $meta_description = _s('Image %i hosted in %w', $image_tr);
        }
    }
    $handler::setVar('meta_description', htmlspecialchars($meta_description ?? ''));
    if ($handler::cond('content_manager') || $is_owner) {
        $handler::setVar('user_items_editor', [
            'user_albums' => $image['user']['albums'] ?? null,
            'type' => 'image',
            'album' => $image['album'] ?? null,
            'category_id' => $image['category_id'] ?? null,
        ]);
    }
    $handler::setVar('share_links_array', get_share_links());
    $handler::setVar('privacy', $image['album']['privacy'] ?? '');
    $embed = [];
    $embed['direct-links'] = [
        'label' => _s('Direct links'),
        'entries' => [
            [
                'label' => _s('Image link'),
                'value' => $image['url_short'],
            ],
            [
                'label' => _s('Image URL'),
                'value' => $image['url'],
            ],
            [
                'label' => _s('Thumbnail URL'),
                'value' => $image['thumb']['url'] ?? '',
            ],
        ],
    ];
    if (isset($image['medium'])) {
        $embed['direct-links']['entries'][] = [
            'label' => _s('Medium URL'),
            'value' => $image['medium']['url'] ?? '',
        ];
    }
    $image_full = [
        'html' => '<img src="' . $image['url'] . '" alt="' . $image['filename'] . '" border="0" />',
        'markdown' => '![' . $image['filename'] . '](' . $image['url'] . ')',
    ];
    $image_full['bbcode'] = html_to_bbcode($image_full['html']);
    $embed['full-image'] = [
        'label' => _s('Full image'),
        'entries' => [
            [
                'label' => 'HTML',
                'value' => htmlentities($image_full['html']),
            ],
            [
                'label' => 'BBCode',
                'value' => $image_full['bbcode'],
            ],
            [
                'label' => 'Markdown',
                'value' => $image_full['markdown'],
            ],
        ],
    ];
    $embed_full_linked['html'] = '<a href="' . $image['url_short'] . '">' . $image_full['html'] . '</a>';
    $embed_full_linked['bbcode'] = html_to_bbcode($embed_full_linked['html']);
    $embed_full_linked['markdown'] = '[![' . $image['filename'] . '](' . $image['url'] . ')](' . $image['url_short'] . ')';
    $embed['full-linked'] = [
        'label' => _s('Full image (linked)'),
        'entries' => [
            [
                'label' => 'HTML',
                'value' => htmlentities($embed_full_linked['html']),
            ],
            [
                'label' => 'BBCode',
                'value' => $embed_full_linked['bbcode'],
            ],
            [
                'label' => 'Markdown',
                'value' => $embed_full_linked['markdown'],
            ],
        ],
    ];
    if (isset($image['medium'])) {
        $embed_medium_linked = [
            'html' => '<a href="' . $image['url_short'] . '"><img src="' . $image['medium']['url'] . '" alt="' . $image['filename'] . '" border="0" /></a>',
        ];
        $embed_medium_linked['bbcode'] = html_to_bbcode($embed_medium_linked['html']);
        $embed_medium_linked['markdown'] = '[![' . $image['medium']['filename'] . '](' . $image['medium']['url'] . ')](' . $image['url_short'] . ')';
        $embed['medium-linked'] = [
            'label' => _s('Medium image (linked)'),
            'entries' => [
                [
                    'label' => 'HTML',
                    'value' => htmlentities($embed_medium_linked['html']),
                ],
                [
                    'label' => 'BBCode',
                    'value' => $embed_medium_linked['bbcode'],
                ],
                [
                    'label' => 'Markdown',
                    'value' => $embed_medium_linked['markdown'],
                ],
            ],
        ];
    }
    $embed_thumb_linked = [
        'html' => '<a href="' . $image['url_short'] . '"><img src="' . $image['thumb']['url'] . '" alt="' . $image['filename'] . '" border="0" /></a>',
    ];
    $embed_thumb_linked['bbcode'] = html_to_bbcode($embed_thumb_linked['html']);
    $embed_thumb_linked['markdown'] = '[![' . $image['thumb']['filename'] . '](' . $image['thumb']['url'] . ')](' . $image['url_short'] . ')';
    $embed['thumb-linked'] = [
        'label' => _s('Thumbnail image (linked)'),
        'entries' => [
            [
                'label' => 'HTML',
                'value' => htmlentities($embed_thumb_linked['html']),
            ],
            [
                'label' => 'BBCode',
                'value' => $embed_thumb_linked['bbcode'],
            ],
            [
                'label' => 'Markdown',
                'value' => $embed_thumb_linked['markdown'],
            ],
        ],
    ];
    $embed_id = 1;
    foreach ($embed as &$v) {
        foreach ($v['entries'] as &$entry) {
            $entry['id'] = 'embed-code-' . $embed_id;
            ++$embed_id;
        }
    }
    $handler::setVar('embed', $embed);
    $addValue = session()['image_view_stock'] ?? [];
    $addValue[] = $id;
    sessionVar()->put('image_view_stock', $addValue);
};
