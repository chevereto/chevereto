<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div id="home-cover-slideshow">
    <?php
        $i = 0;
        foreach (getSetting('homepage_cover_images_shuffled') ?? [] as $k => $v) {
            if ($i > 1 && Handler::cond('mobile_device')) {
                break;
            } ?>
    <div class="home-cover-img" data-src="<?php echo $v['url']; ?>"></div>
    <?php
            $i++;
        }
    ?>
</div>
