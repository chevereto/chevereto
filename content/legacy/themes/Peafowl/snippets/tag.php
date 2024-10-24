<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(
    string $color,
    string $url,
    string $name,
): string {
    $containerClass = 'tag-container';
    return <<<HTML
    <li class="{$containerClass}">
        <a class="tag btn btn-capsule {$color}" href="{$url}" rel="tag">{$name}</a>
    </li>

    HTML;
};
