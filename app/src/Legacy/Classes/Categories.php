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

use Throwable;
use function Chevereto\Legacy\G\get_base_url;

final class Categories
{
    public const CACHE_KEY = 'categories';

    public static function get(): array
    {
        $categories = [];
        $cached = Cache::instance()->get(self::CACHE_KEY);
        if ($cached !== false) {
            return $cached;
        }

        try {
            $columns = [
                'category_id',
                'category_name',
                'category_url_key',
                'category_description',
            ];
            $columnsString = implode(', ', $columns);
            $rows = DB::queryFetchAll(
                "SELECT {$columnsString} FROM "
                    . DB::getTable('categories')
                    . ' ORDER BY category_name ASC;'
            );
            foreach ($rows as $v) {
                $key = $v['category_id'];
                $v['category_url'] = get_base_url('category/' . $v['category_url_key']);
                $categories[$key] = DB::formatRow($v);
            }
        } catch (Throwable) {
        }

        Cache::instance()->set(self::CACHE_KEY, $categories, 3600);

        return $categories;
    }

    public static function deleteCache(): void
    {
        Cache::instance()->delete(self::CACHE_KEY);
    }
}
