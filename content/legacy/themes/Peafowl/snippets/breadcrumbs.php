<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}

return function(array $items): string {
    $itemsHtml = '';
    $lastKey = array_key_last($items);
    foreach($items as $pos => $item) {
        if($pos === $lastKey) {
            $itemHtml = <<<HTML
            <span aria-current="page" data-text="album-name">{$item['name_html']}</span>
            HTML;
        } else {
            $itemHtml = <<<HTML
            <a href="{$item['url']}">{$item['name_html']}</a>
            HTML;
        }
        $itemsHtml .= <<<HTML
        <li>{$itemHtml}</li>
        HTML;
    }
    return <<<HTML
    <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
            {$itemsHtml}
        </ol>
    </nav>
    HTML;
};
