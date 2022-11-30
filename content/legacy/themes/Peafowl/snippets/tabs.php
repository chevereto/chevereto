<?php

use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$tabs = Handler::var('tabs') ?? [];

foreach ($tabs as $tab) {
    if ($tab["current"]) {
        $current = $tab;

        break;
    }
}
?><div class="phone-display-inline-block phablet-display-inline-block hidden tab-menu --hide" data-action="tab-menu">
    <span class="btn-icon <?php echo $current['icon'] ?? ''; ?>" data-content="tab-icon"></span><span class="btn-text" data-content="current-tab-label"><?php echo $current["label"] ?? ''; ?></span><span class="tab-menu--hide btn-icon fas fa-angle-down"></span><span class="tab-menu--show btn-icon fas fa-angle-up"></span>
</div>
<div class="content-tabs-container content-tabs-container--mobile phone-display-block phablet-display-block">
    <div class="content-tabs-shade phone-show phablet-show hidden"></div>
    <div class="content-tabs-wrap">
        <ul class="content-tabs">
            <?php
                foreach ($tabs as $tab) {
                    $tabClass = $tab['class'] ?? '';
                    if (($tab["current"] ?? false)) {
                        $tabClass .= ' current';
                    }
                    $tabClass = trim($tabClass);
                    $echo = [
                        '<li class="' . $tabClass . '">',
                        '<a ',
                        isset($tab['id']) ? ('id="' . $tab['id'] . '-link" data-tab="' . $tab["id"] . '" ') : '',
                        'href="' . ($tab['url'] ?? '') . '">',
                        '<span class="btn-icon ' . ($tab['icon'] ?? '') . '"></span>',
                        '<span class="btn-text">' . $tab["label"] . '</span>',
                        '</a></li>' . "\n"
                    ];
                    echo implode('', $echo);
                }
            ?>
        </ul>
    </div>
</div>
