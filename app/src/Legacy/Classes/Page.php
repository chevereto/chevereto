<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Chevereto\Config\Config;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Vars\get;
use function Chevereto\Vars\post;

class Page
{
    public static array $table_fields = [
        'url_key',
        'type',
        'link_url',
        'icon',
        'title',
        'description',
        'keywords',
        'is_active',
        'is_link_visible',
        'attr_target',
        'attr_rel',
        'sort_display',
        'internal',
        'code',
    ];

    public static function getSingle(string $var, string $by = 'url_key', bool $withCode = true): array
    {
        return [];
    }

    public static function getAll(array $args = [], array $sort = [], bool $withCode = false): array
    {
        return [];
    }

    public static function get(array $values, array $sort = [], ?int $limit = null, bool $withCode = false): array
    {
        return [];
    }

    public static function getPath(?string $var = null): string
    {
        return PATH_PUBLIC_CONTENT_PAGES . (is_string($var) ? $var : '');
    }

    public static function getFields(): array
    {
        $fields = self::$table_fields;
        if (Config::enabled()->phpPages()) {
            $fields[] = 'file_path';
        }

        return $fields;
    }

    public static function update(int $id, array $values): int
    {
        return 0;
    }

    public static function writePage(array $args = []): bool
    {
        return false;
    }

    public static function fill(array &$page): void
    {
        $page['title_html'] = safe_html($page['title'] ?? '');
        $type_tr = [
            'internal' => _s('Internal'),
            'link' => _s('Link'),
        ];
        $page['type_tr'] = $type_tr[$page['type']];
        switch ($page['type']) {
            case 'internal':
                $page['url'] = get_base_url('page/' . $page['url_key']);
                if (empty($page['file_path'])) {
                    $filepaths = [
                        'default' => 'default/',
                        'user' => null, // base
                    ];
                    $file_basename = $page['url_key'] . '.php';
                    foreach ($filepaths as $v) {
                        if (is_readable(self::getPath($v) . $file_basename)) {
                            $page['file_path'] = $v . $file_basename;
                        }
                    }
                } elseif (Config::enabled()->phpPages()) {
                    if ($page['internal'] === 'contact'
                        && (post() !== [] || (get()['sent'] ?? '0' == '1'))
                    ) {
                        $page['file_path'] = 'default/contact.php';
                    }
                }
                $page['file_path_absolute'] = self::getPath($page['file_path']);
                if (Config::enabled()->phpPages()) {
                    if (! file_exists($page['file_path_absolute'])) {
                        self::writePage([
                            'file_path' => $page['file_path'],
                            'code' => $page['code'] ?? '',
                        ]);
                    }
                }

                break;
            case 'link':
                $page['url'] = is_url($page['link_url']) || str_starts_with($page['link_url'], '/')
                    ? $page['link_url']
                    : '';

                break;
        }
        $page['link_attr'] = 'href="' . $page['url'] . '"';
        if ($page['attr_target'] !== '_self') {
            $page['link_attr'] .= ' target="' . $page['attr_target'] . '"';
        }
        if (! empty($page['attr_rel'])) {
            $page['link_attr'] .= ' rel="' . $page['attr_rel'] . '"';
        }
        if (! empty($page['icon'])) {
            $page['title_html'] = '<span class="btn-icon ' . $page['icon'] . '"></span> ' . $page['title_html'];
        }
    }

    public static function formatRowValues(mixed &$values, mixed $row = []): void
    {
    }

    public static function insert(array $values = []): int
    {
        return 0;
    }

    public static function delete(array|int $page): int
    {
        return 0;
    }
}
