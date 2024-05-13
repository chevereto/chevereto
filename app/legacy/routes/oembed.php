<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\json_output;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\set_status_header;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\G\str_replace_last;
use function Chevereto\Legacy\G\xml_output;
use function Chevereto\Legacy\getIdFromURLComponent;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\get;

return function (Handler $handler) {
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    }
    $viewer = Image::getUrlViewer('%id');
    $viewer = str_replace('/', '\/', $viewer);
    $regex = str_replace_last('%id', '(.*)', $viewer);
    $regex = str_replace_first('https:', 'https?:', $regex);
    $regex = str_replace_first('http:', 'https?:', $regex);
    if (!preg_match('#^' . $regex . '$#', get()['url'] ?? '', $matches)) {
        set_status_header(403);
        die();
    }
    $id = getIdFromURLComponent($matches[1]);
    if ($id == 0) {
        set_status_header(404);
        die();
    }
    $image = Image::getSingle(id: $id, pretty: true);
    if ($image === []) {
        set_status_header(404);
        die();
    }
    if (!$image['is_approved']) {
        set_status_header(403);
        die();
    }
    if (in_array($image['album']['privacy'] ?? '', ['password', 'private', 'custom'])) {
        set_status_header(401);
        die();
    }
    if (($image['user']['is_private'] ?? false) == 1) {
        unset($image['user']);
        $image['user'] = User::getPrivate();
    }
    $data = [
        'version' => '1.0',
        'provider_name' => safe_html(Settings::get('website_name')),
        'provider_url' => get_public_url(),
        'title' => safe_html($image['title']),
        'web_page' => $image['url_viewer'],
        'width' => $image['width'],
        'height' => $image['height'],
    ];
    switch ($image['type']) {
        case 'video':
            $data['html'] = '<video src="' . $image['url'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" controls poster="' . $image['url_frame'] . '"></video>';
            $data['type'] = 'video';

            break;
        case 'image':
            $data['url'] = $image['display_url'];
            $data['type'] = 'photo';

            break;
    }
    if (isset($image['user'])) {
        $data = array_merge($data, [
            'author_name' => safe_html($image['user']['username']),
            'author_url' => $image['user']['url'],
        ]);
    }
    $thumb = 'display_url';
    $maxWidth = isset(get()['maxwidth']) ? (int) get()['maxwidth'] : $image['width'];
    $maxHeight = isset(get()['maxHeight']) ? (int) get()['maxHeight'] : $image['height'];
    if ($image['display_width'] > $maxWidth || $image['display_height'] > $maxHeight) {
        $thumb = null;
        if (getSetting('upload_thumb_width') <= $maxWidth && getSetting('upload_thumb_height') <= $maxHeight) {
            $thumb = 'thumb';
        }
    }
    if ($thumb !== null) {
        if ($thumb == 'thumb') {
            $display_url = $image['thumb']['url'];
            $display_width = (int) getSetting('upload_thumb_width');
            $display_height = (int) getSetting('upload_thumb_height');
        } else {
            $display_url = $image['display_url'];
            $display_width = (int) $image['display_width'];
            $display_height = (int) $image['display_height'];
        }
        $data = array_merge($data, [
            'thumbnail_url' => $display_url,
            'thumbnail_width' => $display_width,
            'thumbnail_height' => $display_height,
        ]);
    }
    match (get()['format'] ?? '') {
        'xml' => xml_output(['oembed' => $data]),
        default => json_output($data),
    };
    die();
};
