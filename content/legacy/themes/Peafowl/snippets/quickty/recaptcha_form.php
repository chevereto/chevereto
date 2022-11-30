<?php

use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php
if (Handler::cond('captcha_needed') && Handler::hasVar('captcha_html')) {
    ?>
<div class="content-section content-section--captcha-fix">
    <?php echo Handler::var('captcha_html'); ?>
</div>
<?php
} ?>
