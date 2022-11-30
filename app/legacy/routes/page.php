<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Page;
use function Chevereto\Legacy\G\add_ending_slash;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Vars\env;

return function (Handler $handler) {
    if (!(bool) env()['CHEVERETO_ENABLE_PAGES']) {
        $this->issueError(404);

        return;
    }
    $request_url_key = implode('/', $handler->request());
    $page = Page::getSingle($request_url_key);
    if (!$page || !$page['is_active'] || $page['type'] !== 'internal') {
        $handler->issueError(404);

        return;
    }
    if (!$page['file_path_absolute']) {
        $handler->issueError(404);

        return;
    }
    if (!file_exists($page['file_path_absolute'])) {
        $handler->issueError(404);

        return;
    }
    $pathinfo = pathinfo($page['file_path_absolute']);
    $handler->setPathTheme(add_ending_slash($pathinfo['dirname']));
    $handler->setTemplate($pathinfo['filename']);
    $page_metas = [
        'pre_doctitle' => $page['title'],
        'meta_description' => htmlspecialchars($page['description'] ?? ''),
        'meta_keywords' => htmlspecialchars($page['keywords'] ?? '')
    ];
    foreach ($page_metas as $k => $v) {
        if ($v === null) {
            continue;
        }
        $handler->setVar($k, $v);
    }
};
