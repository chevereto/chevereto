<?php
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<style>
    .background-cover {
        background-image: url(<?php echo getSetting('homepage_cover_images')[0]['url'] ?? ''; ?>);
    }
</style>
<div class="background-cover"></div>
