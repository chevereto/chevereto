<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(array $tags, string $class = '--tags-icon'): string {
    $count = count($tags);
    $tagFn = include __DIR__ . '/tag.php';
    $contentTagsClass = 'content-tags';
    if ($class !== '') {
        $contentTagsClass .= ' ' . $class;
    }
    $return = <<<HTML
    <ul class="{$contentTagsClass}" data-content="tags" data-count="{$count}">

    HTML;
    foreach ($tags as $tag) {
        $return .= $tagFn(
            color: 'default',
            url: $tag['url'],
            name: $tag['name_safe_html'],
        );
    }
    $return .= <<<HTML
    </ul>
    HTML;

    return $return;
};
