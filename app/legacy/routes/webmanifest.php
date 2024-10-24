<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\getSetting;

return function (Handler $handler) {
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    }
    header('Content-Type: application/json');
    $manifest = [
        'name' => getSetting('website_doctitle'),
        'short_name' => getSetting('website_name'),
        'description' => getSetting('website_description'),
        // 'lang' => 'en-US',
        // 'dir' => 'ltr',
        'display' => 'standalone',
        'scope' => get_base_url(''),
        'id' => get_base_url('?_pwa=1'),
        'start_url' => get_base_url('?_pwa=1'),
        // 'background_color' => '#FFFFFF',
        // 'theme_color' => '#FFFFFF',
    ];
    echo json_encode($manifest, JSON_PRETTY_PRINT);
    exit();
};
