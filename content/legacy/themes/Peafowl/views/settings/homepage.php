<?php

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div class="input-label">
    <label for="homepage_title_html"><?php _se('Title'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="homepage_title_html" id="homepage_title_html" class="text-input r2 resize-vertical" placeholder="<?php echo Handler::var('user')['name']; ?>"><?php echo Settings::get('homepage_title_html'); ?></textarea></div>
    <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["homepage_title_html"] ?? ''; ?></span>
</div>
<div class="input-label">
    <label for="homepage_paragraph_html"><?php _se('Paragraph'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="homepage_paragraph_html" id="homepage_paragraph_html" class="text-input r2 resize-vertical" placeholder="<?php _se('Feel free to browse and discover all my shared images and albums.'); ?>"><?php echo Settings::get('homepage_paragraph_html'); ?></textarea></div>
    <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["homepage_paragraph_html"] ?? ''; ?></span>
</div>
<div class="input-label">
    <label for="homepage_cta_html"><?php _se('Button'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="homepage_cta_html" id="homepage_cta_html" class="text-input r2 resize-vertical" placeholder="<?php _se('View all my images'); ?>"><?php echo Settings::get('homepage_cta_html'); ?></textarea></div>
    <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["homepage_cta_html"] ?? ''; ?></span>
</div>
