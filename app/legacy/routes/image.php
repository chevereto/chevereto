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
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\flatten_array;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_global;
use function Chevereto\Legacy\G\redirect;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Legacy\getComments;
use function Chevereto\Legacy\getFriendlyExif;
use function Chevereto\Legacy\getIdFromURLComponent;
use function Chevereto\Legacy\getIpButtonsArray;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\headersNoCache;
use function Chevereto\Legacy\isShowEmbedContent;
use function Chevereto\Legacy\virtualRouteHandleRedirect;
use function Chevereto\Vars\env;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    virtualRouteHandleRedirect('image', $handler->requestArray()[0]);
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
    if (! isset(session()['image_view_stock'])) {
        sessionVar()->put('image_view_stock', []);
    }
    $logged_user = Login::getUser();
    User::statusRedirect($logged_user['status'] ?? null);
    $image = Image::getSingle(
        $id,
        ! in_array($id, session()['image_view_stock']),
        true,
        $logged_user
    );
    if ($image === [] || ! isset($image['url'])) {
        $handler->issueError(404);

        return;
    }
    if (! (bool) env()['CHEVERETO_ENABLE_USERS']
        && ($image['user']['id'] ?? 'not-found') != getSetting('website_mode_personal_uid')) {
        $handler->issueError(404);

        return;
    }
    if ($handler->isRequestLevel(3)) {
        if ($request_handle[1] === 'delete') {
            $password = $request_handle[2] ?? '';
            if (Image::verifyPassword($id, $password)) {
                Image::delete($id);
                redirect($image['path_viewer'] . '?deleted', 301);
            }
        }

        $handler->issueError(404);

        return;
    }
    if (! $image['is_approved']
        && (! ($logged_user['is_manager'] ?? false) && ! ($logged_user['is_admin'] ?? false))
    ) {
        $handler->issueError(403);

        return;
    }
    if ($image['path_viewer'] != get_current_url(true, ['lang'])) {
        redirect($image['path_viewer'], 302);
    }
    $handler::setVar('canonical', $image['url_viewer']);
    if ((! $handler::cond('content_manager')
            && ($image['user']['status'] ?? null) == 'banned')
    ) {
        $handler->issueError(404);

        return;
    }
    sessionVar()->put('last_viewed_image', encodeID((int) $id));
    $is_owner = isset($image['user']['id']) ? ($image['user']['id'] == ($logged_user['id'] ?? null)) : false;
    if (getSetting('website_privacy_mode') == 'private') {
        if ($handler::cond('forced_private_mode')) {
            $image['album']['privacy'] = getSetting('website_content_privacy_mode');
        }
        if (! Login::getUser()
            && ($image['album']['privacy'] ?? null) != 'private_but_link'
        ) {
            headersNoCache();
            redirect('login', 302);
        }
    }
    if (! $handler::cond('content_manager')
        && ! $is_owner
        && ($image['album']['privacy'] ?? null) == 'password'
        && ! Album::checkSessionPassword($image['album'])
    ) {
        sessionVar()->put('redirect_password_to', $image['path_viewer']);
        headersNoCache();
        redirect($image['album']['url'], 302);
    }
    if (isset($image['user']['is_private'])
        && $image['user']['is_private'] == 1
        && ! $handler::cond('content_manager')
        && $image['user']['id'] != ($logged_user['id'] ?? null)
    ) {
        unset($image['user']);
        $image['user'] = User::getPrivate();
    }
    if (! $handler::cond('content_manager')
        && in_array($image['album']['privacy'] ?? null, ['private', 'custom'])
        && ! $is_owner
    ) {
        $handler->issueError(404);

        return;
    }
    if (isset($image['user']['id'])
        && ($handler::cond('content_manager') || $is_owner)) {
        $image['user']['albums'] = User::getAlbums((int) $image['user']['id']);
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
        'icon' => 'fas fa-list-ul',
        'label' => _s('About'),
        'id' => 'tab-about',
        'current' => true,
        'url' => '#about',
    ];
    $comments = getComments();
    if ($comments !== '') {
        $tabs[] = [
            'icon' => 'fas fa-comments',
            'label' => _s('Comments'),
            'id' => 'tab-comments',
            'url' => '#comments',
        ];
    }
    $handler::setVar('comments', $comments);
    if (isShowEmbedContent()) {
        $tabs[] = [
            'icon' => 'fas fa-code',
            'label' => _s('Embed codes'),
            'id' => 'tab-embeds',
            'url' => '#embeds',
        ];
    }
    $image_exif = [];
    if (getSetting('theme_show_exif_data')) {
        $image_exif = getFriendlyExif($image['original_exifdata']) ?? [];
        if ($image_exif !== []) {
            $tabs[] = [
                'icon' => 'fas fa-camera',
                'label' => _s('EXIF data'),
                'id' => 'tab-exif',
                'url' => '#exif',
            ];
        }
    }
    $handler::setVar('image_exif', $image_exif);
    if ($handler::cond('content_manager')) {
        if ($handler::cond('admin')) {
            $tabs[] = [
                'icon' => 'fas fa-info-circle',
                'label' => _s('Info'),
                'id' => 'tab-info',
                'url' => '#info',
            ];
        }
        $bannedIp = IpBan::getSingle([
            'ip' => $image['uploader_ip'],
        ]);
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
    $firstTabSetting = getSetting('image_first_tab');
    if (! $handler::cond('admin') && $firstTabSetting == 'info') {
        $firstTabSetting = 'embeds';
    }
    if (! isShowEmbedContent() && $firstTabSetting == 'embeds') {
        $firstTabSetting = 'about';
    }
    if ($comments === '' && $firstTabSetting == 'comments') {
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
        if ($currentTab !== []) {
            array_unshift($tabs, $currentTab);
        }
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
            '%i' => $image[$image['title'] === null ? 'filename' : 'title'],
            '%a' => $image['album']['name'] ?? '',
            '%w' => getSetting('website_name'),
            '%c' => $image['category']['name'] ?? '',
        ];
        if (isset($image['album']['id'])
            || (
                ! ((bool) ($image['user']['is_private'] ?? false)) && isset($image['album']['name'])
            )) {
            $meta_description = _s('Image %i in %a album', $image_tr);
        } elseif (isset($image['category']['id'])) {
            $meta_description = _s('Image %i in %c category', $image_tr);
        } else {
            $meta_description = _s('Image %i hosted in %w', $image_tr);
        }
    }
    $handler::setVar('meta_description', $meta_description ?? '');
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
    require_theme_file('snippets/embed');
    $embed_share_tpl = get_global('embed_share_tpl');
    $sharing = [];
    foreach (flatten_array($image) as $imageKey => $imageValue) {
        $sharing['%' . strtoupper($imageKey) . '%'] = $imageValue;
    }
    $embed = [];
    $hasSizes = [
        'frame' => $image['url_frame'] !== '',
        'medium' => $image['medium']['url'] !== null,
        'thumb' => $image['thumb']['url'] !== null,
    ];
    foreach ($embed_share_tpl as $code => $group) {
        $entries = [];
        $groupLabel = $group['label'];
        foreach ($group['options'] as $option => $optionValue) {
            foreach ($hasSizes as $sizeKey => $sizeValue) {
                if (! $sizeValue && str_starts_with($option, $sizeKey . '-')) {
                    continue 2;
                }
            }
            $value = $optionValue['template'];
            if (is_array($value)) {
                $value = $value[$image['type']];
            }
            $value = strtr($value, $sharing);
            if ($value === '') {
                continue;
            }
            if (str_contains($option, 'html')) {
                $value = htmlentities($value);
            }
            $label = $optionValue['label'];
            $label = str_ireplace($groupLabel, '', $label);
            $label = ucfirst(trim($label));
            $entry = [
                'label' => $label,
                'value' => $value,
                'id' => $option,
            ];
            if ($code === 'links'
                && ! in_array($option, ['viewer-links', 'delete-links'])
            ) {
                $entry['url_download'] = $value;
            }
            $entries[] = $entry;
        }
        $embed[$code] = [
            'label' => $group['label'],
            'entries' => $entries,
        ];
    }
    $handler::setVar('oembed', [
        'title' => $handler::var('pre_doctitle'),
        'url' => $image['url_viewer'],
    ]);
    $handler::setVar('embed', $embed);
    $addValue = session()['image_view_stock'] ?? [];
    $addValue[] = $id;
    sessionVar()->put('image_view_stock', $addValue);
    if ($image['tags_string'] !== '') {
        $handler::setVar('meta_keywords', $image['tags_string']);
    }
};
