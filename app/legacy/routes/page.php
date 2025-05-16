<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Cache;
use Chevereto\Legacy\Classes\Page;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\str_replace_last;
use function Chevereto\Vars\env;

return function (Handler $handler) {
    if (! (bool) env()['CHEVERETO_ENABLE_PAGES']) {
        $handler->issueError(404);

        return;
    }
    $urlKey = implode('/', $handler->request());
    $cacheKey = Page::getCacheKey($urlKey);
    $page = Cache::instance()->get($cacheKey);
    if ($page === false) {
        $page = Page::getSingle($urlKey);
        if ($page !== []) {
            Cache::instance()->set($cacheKey, $page, 3600);
        }
    }
    if (! $page || ! $page['is_active'] || $page['type'] !== 'internal') {
        $handler->issueError(404);

        return;
    }
    if ((bool) env()['CHEVERETO_ENABLE_PHP_PAGES']) {
        if (! fileExists($page['file_path_absolute'] ?? null)) {
            $handler->issueError(404);

            return;
        }
        $pathinfo = pathinfo($page['file_path_absolute']);
        $handler->setPathTheme(add_ending_slash($pathinfo['dirname']));
        $handler->setTemplate($pathinfo['filename']);
    } else {
        if ($page['code'] === null) {
            $file = str_replace_last('.php', '.html', $page['file_path_absolute']);
            if (fileExists($file)) {
                $page['code'] = file_get_contents($file);
            }
            if ($page['code'] !== null) {
                Page::update($page['id'], [
                    'code' => $page['code'],
                ]);
            }
        }
        $handler->setContent($page['code'] ?? '');
    }
    $page_metas = [
        'pre_doctitle' => $page['title'],
        'meta_description' => htmlspecialchars($page['description'] ?? ''),
        'meta_keywords' => htmlspecialchars($page['keywords'] ?? ''),
    ];
    foreach ($page_metas as $k => $v) {
        if ($v === null) {
            continue;
        }
        $handler->setVar($k, $v);
    }
};

function fileExists(?string $file): bool
{
    if ($file === null || $file === '') {
        return false;
    }

    return file_exists($file);
}
