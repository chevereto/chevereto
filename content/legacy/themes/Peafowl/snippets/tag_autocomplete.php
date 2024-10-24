<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(string $name): string
{
    return <<<HTML
    <li class="tag-container">
        <button class="tag btn btn-capsule default" rel="tag">{$name}</button>
    </li>

    HTML;
};
