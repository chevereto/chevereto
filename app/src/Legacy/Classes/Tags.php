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

final class Tags
{
    public const CACHE_KEY = 'tags_top';

    public static function top(): array
    {
        $tagsTop = [];
        $cached = Cache::instance()->get(self::CACHE_KEY);
        if ($cached !== false) {
            return $cached;
        }

        try {
            $tagsTable = DB::getTable('tags');
            $rows = DB::queryFetchAll(
                <<<SQL
                SELECT t.tag_name `name`, t.tag_id `id`, t.tag_files `files`, t.tag_views `views`
                FROM `{$tagsTable}` t
                ORDER BY `tag_files` DESC, `tag_name` ASC
                LIMIT 30;
                SQL
            );
            foreach ($rows as $k => $v) {
                $tag = array_merge($v, Tag::row($v['name']));
                $tagsTop[] = $tag;
            }
        } catch (Throwable) {
        }

        Cache::instance()->set(self::CACHE_KEY, $tagsTop, 1800);

        return $tagsTop;
    }
}
