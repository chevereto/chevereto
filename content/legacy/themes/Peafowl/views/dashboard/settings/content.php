<?php

use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\inputDisabledPaid;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('content', _s('Content')); ?>
<div class="input-label">
    <label for="show_nsfw_in_listings"><?php _se('Show not safe content in listings'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="show_nsfw_in_listings" id="show_nsfw_in_listings" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('show_nsfw_in_listings')); ?>
        </select></div>
    <div class="input-below"><?php _se("Enable this if you want to show not safe content in listings."); ?> <?php _se('Can be overridden by user own settings.'); ?> <?php _se("This setting doesn't affect administrators."); ?></div>
</div>
<div class="input-label">
    <label for="theme_nsfw_blur"><?php _se('Blur NSFW content in listings'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_nsfw_blur" id="theme_nsfw_blur" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_nsfw_blur')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to apply a blur effect on the NSFW images in listings.'); ?></div>
</div>
<div class="input-label">
    <label for="show_nsfw_in_random_mode"><?php _se('Show not safe content in random mode'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="show_nsfw_in_random_mode" id="show_nsfw_in_random_mode" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Handler::var('safe_post') ? Handler::var('safe_post')['show_nsfw_in_random_mode'] : Settings::get('show_nsfw_in_random_mode')); ?>
        </select></div>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="show_banners_in_nsfw"><?php _se('Show banners in not safe content'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="show_banners_in_nsfw" id="show_banners_in_nsfw" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('show_banners_in_nsfw')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show banners in not safe content pages.'); ?></div>
</div>
<div class="input-label">
    <?php echo badgePaid('lite'); ?><label for="image_lock_nsfw_editing"><?php _se('Lock %s editing', _s('NSFW')); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('lite'); ?> type="text" name="image_lock_nsfw_editing" id="image_lock_nsfw_editing" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([
                    0 => _s('Disabled'),
                    1 => _s('Enabled'),
                ], Settings::get('image_lock_nsfw_editing')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this to prevent users from changing the NSFW flag. When enabled, only admin and managers will have this permission.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="stop_words"><?php _se('Stop words'); ?></label>
    <div class="c14 phablet-c1"><textarea <?php echo inputDisabledPaid('pro'); ?> name="stop_words" id="stop_words" class="r4 resize-none" placeholder="<?php _se('One rule per line'); ?>"><?php echo Settings::get('stop_words'); ?></textarea></div>
    <div class="input-below"><?php _se("Define words that won't be allowed for content."); ?></div>
</div>
