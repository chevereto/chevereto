<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(array $tags, array $tags_active = [], string $class = '--tags-icon'): string {
    $count = count($tags);
    $tagFn = include __DIR__ . '/tag_filter.php';
    $tagNames = array_column($tags, 'name');
    $hasActiveFilter = count(array_intersect($tagNames, $tags_active)) > 0;
    $contentTagsClass = 'content-tags';
    if($hasActiveFilter) {
        $contentTagsClass .= ' --filtering';
//         <li class="content-tags-label" title="{$title}"><i class="fas fa-tags"></i></li>
    }
    if($class !== '') {
        $contentTagsClass .= ' ' . $class;
    }
    $return = <<<HTML
    <ul class="{$contentTagsClass}" data-content="tags" data-count="{$count}">

    HTML;
    foreach ($tags as $tag) {
        $isActive = in_array($tag['name'], $tags_active);
        $return .= $tagFn(
            color: $isActive
                ? 'active'
                : 'default',
            url: $tag['url'],
            name: $tag['name_safe_html'],
            isActive: $isActive,
            urlAppend: $tag['url_append'] ?? '',
            urlRemove: $tag['url_remove'] ?? ''
        );
    }
    $return .= <<<HTML
    </ul>
    HTML;

    return $return;
};
