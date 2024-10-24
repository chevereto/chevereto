<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(
    string $color,
    string $url,
    string $name,
    bool $isActive = false,
    string $urlAppend = '',
    string $urlRemove = ''
): string {
    $containerClass = 'tag-container';
    if($isActive) {
        $containerClass .= ' --active';
    }
    $linkAppend = '';
    $linkRemove = '';
    if($urlAppend !== '') {
        $titleAppend = _s('Add %s to filter', $name);
        $linkAppend = <<<HTML
        <a title="{$titleAppend}" class="tag-append" href="{$urlAppend}" rel="tag-filter"><i class="fas fa-circle-plus"></i></a>

        HTML;
    }
    if($urlRemove !== '') {
        $titleRemove = _s('Remove %s from filter', $name);
        $linkRemove = <<<HTML
        <a title="{$titleRemove}" class="tag-remove" href="{$urlRemove}" rel="tag-filter"><i class="fas fa-circle-minus"></i></a>

        HTML;
    }
    return <<<HTML
    <li class="{$containerClass}">
        <a class="tag btn btn-capsule {$color}" href="{$url}" rel="tag">{$name}</a>
        {$linkAppend}
        {$linkRemove}
    </li>

    HTML;
};
