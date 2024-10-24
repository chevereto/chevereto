<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Listing;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Tag;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\get_share_links;
use function Chevereto\Vars\env;
use function Chevereto\Vars\get;
use function Chevereto\Vars\request;
use function Chevereto\Vars\server;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

return function (Handler $handler) {
    if (! $handler::cond('explore_enabled')) {
        $handler->issueError(404);

        return;
    }
    $tagKey = $handler->request()[0] ?? '';
    $tagKey = rawurldecode($tagKey);
    if ($tagKey === '') {
        $handler->issueError(404);

        return;
    }
    $tagsPathAsIs = explode(',', $tagKey);
    $tagsParsed = Tag::parse($tagKey);
    if ($tagsParsed === []) {
        $handler->issueError(404);

        return;
    }
    if (count($tagsParsed) !== count($tagsPathAsIs)) {
        $handler->issueError(404);

        return;
    }
    if (! in_array(get()['match'] ?? '', ['', 'all'])) {
        $handler->issueError(400);

        return;
    }
    if (env()['CHEVERETO_MAX_TAGS_PER_LISTING'] !== '0'
        && count($tagsParsed) > (int) env()['CHEVERETO_MAX_TAGS_PER_LISTING']
    ) {
        $handler->issueError(403);

        return;
    }
    $tags = Tag::get($tagKey, 'id', 'name', 'description');
    if (count($tags) !== count($tagsPathAsIs)) {
        $handler->issueError(404);

        return;
    }
    if (! $tags) {
        $handler->issueError(404);

        return;
    }
    if (! isset(session()['tag_view_stock'])) {
        sessionVar()->put('tag_view_stock', []);
    }
    $sessionValue = session()['tag_view_stock'];
    $sumViews = [];
    foreach ($tags as &$tag) {
        $tag = array_merge($tag, Tag::row($tag['name']));
        if (! in_array($tag['id'], session()['tag_view_stock'])) {
            $sessionValue[] = $tag['id'];
            $sumViews[] = $tag['id'];
        }
    }
    $tags_names = array_column($tags, 'name');
    $tag_string = implode(', ', $tags_names);
    $tag_string_no_spaces = implode(',', $tags_names);
    $tags_id = array_column($tags, 'id');
    $tags_id_encoded = array_map(function ($id): string {
        return encodeID($id);
    }, $tags_id);
    $tags_key_display = $tag_string;
    $tags_key_url = $tag_string_no_spaces;
    $tags_basename = get_route_name() . '/' . rawurlencode($tags_key_url);
    $canonical = get_base_url($tags_basename, true);
    $queryString = server()['QUERY_STRING'] ?? '';
    $tags_descriptions = array_column($tags, 'description');
    $tags_descriptions = array_filter($tags_descriptions);
    if ($queryString !== '') {
        parse_str($queryString, $parse);
        unset($parse['lang']);
        $queryString = http_build_query($parse ?? []);
        if ($queryString !== '') {
            $canonical .= '/?' . $queryString;
        }
    }
    $handler::setVar('canonical', $canonical);
    $handler::setVar('pre_doctitle', $tags_key_display);
    $getParams = Listing::getParams(request());
    $tabs = Listing::getTabs([
        'listing' => 'images',
        'basename' => $tags_basename,
        'params_hidden' => [
            'tag_id' => implode(',', $tags_id_encoded),
            'tag_match' => get()['match'] ?? 'any',
            'hide_banned' => 1,
        ],
    ], $getParams);
    $handler::setVar('list_params', $getParams);
    $listing = new Listing();
    $listing->setType('images');
    if (isset($getParams['reverse'])) {
        $listing->setReverse($getParams['reverse']);
    }
    if (isset($getParams['seek'])) {
        $listing->setSeek($getParams['seek']);
    }
    $listing->setOffset($getParams['offset']);
    $listing->setLimit($getParams['limit']); // how many results?
    $listing->setSortType($getParams['sort'][0]); // date | size | views
    $listing->setSortOrder($getParams['sort'][1]); // asc | desc
    $listing->setTagsIds(...$tags_id);
    $listing->setTagsString($tag_string_no_spaces);
    $listing->setTagsMatch(get()['match'] ?? 'any');
    $listing->setRequester(Login::getUser());
    $listing->exec();
    $tags_descriptions = implode(' — ', $tags_descriptions);
    $handler::setVar('tags_descriptions', $tags_descriptions);
    $handler::setVar('meta_description', $tags_descriptions);
    $handler::setVar('tags', $tags);
    $handler::setVar('tabs', $tabs);
    $handler::setVar('listing', $listing);
    $handler::setVar('share_links_array', get_share_links());
    if ($sumViews !== []) {
        $tagsTable = DB::getTable('tags');
        $tagsIds = implode(',', $sumViews);
        $sumViewsSql = <<<MySQL
        UPDATE {$tagsTable} SET `tag_views` = `tag_views` + 1 WHERE `tag_id` IN ({$tagsIds});

        MySQL;
        $db = DB::getInstance();
        $db->query($sumViewsSql);
        $db->exec();
        sessionVar()->put('tag_view_stock', $sessionValue);
    }
    $handler::setVar('meta_keywords', $tag_string);
};
