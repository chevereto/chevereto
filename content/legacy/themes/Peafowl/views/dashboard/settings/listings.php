<?php

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('listings', _s('Listings')); ?>
<div class="input-label">
    <label for="listing_items_per_page"><?php _se('List items per page'); ?></label>
    <div class="c2"><input type="number" min="1" name="listing_items_per_page" id="listing_items_per_page" class="text-input" value="<?php echo Settings::get('listing_items_per_page'); ?>" placeholder="<?php echo Settings::getDefault('listing_items_per_page'); ?>" required></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['listing_items_per_page'] ?? ''; ?></div>
    <div class="input-below"><?php _se('How many items should be displayed per page listing.'); ?></div>
</div>
<div class="input-label">
    <label for="listing_pagination_mode"><?php _se('List pagination mode'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="listing_pagination_mode" id="listing_pagination_mode" class="text-input">
            <?php
            echo get_select_options_html(['endless' => _s('Endless scrolling'), 'classic' => _s('Classic pagination')], Settings::get('listing_pagination_mode')); ?>
        </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['listing_pagination_mode'] ?? ''; ?></div>
    <div class="input-below"><?php _se('What pagination method should be used.'); ?></div>
</div>
<div class="input-label">
    <label for="listing_viewer"><?php _se('Listing viewer'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="listing_viewer" id="listing_viewer" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Handler::var('safe_post') ? Handler::var('safe_post')['listing_viewer'] : Settings::get('listing_viewer')); ?>
        </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['listing_viewer'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Enable this to use the listing viewer when clicking on an image.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_image_listing_sizing"><?php _se('Image listing size'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_image_listing_sizing" id="theme_image_listing_sizing" class="text-input">
        <?php
            echo get_select_options_html(
    ['fluid' => _s('Fluid'), 'fixed' => _s('Fixed')],
    Handler::var('safe_post')
                        ? Handler::var('safe_post')['theme_image_listing_sizing']
                        : Settings::get('theme_image_listing_sizing')
); ?>
        </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['theme_image_listing_sizing'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Both methods use a fixed width but fluid method uses automatic heights.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="explore_albums_min_image_count"><?php _se('Album listing images requirement'); ?> (<?php echo _se('explore'); ?>)</label>
    <div class="c2"><input type="number" min="1" name="explore_albums_min_image_count" id="explore_albums_min_image_count" class="text-input" value="<?php echo Settings::get('explore_albums_min_image_count'); ?>" placeholder="<?php echo Settings::getDefault('explore_albums_min_image_count'); ?>" required></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['explore_albums_min_image_count'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Sets the minimum image count needed to show albums in explore.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label><?php _se('Listing columns number'); ?></label>
    <div class="input-below"><?php _se('Here you can set how many columns are used based on each target device.'); ?></div>
    <div class="overflow-auto margin-bottom-10 margin-top-10">
        <label for="listing_columns_phone" class="c2 float-left input-line-height"><?php _se('Phone'); ?></label>
        <input type="number" name="listing_columns_phone" id="listing_columns_phone" class="text-input c2" value="<?php echo Settings::get('listing_columns_phone'); ?>" placeholder="<?php echo Settings::getDefault('listing_columns_phone'); ?>" pattern="\d*" min="1" max="7" required>
    </div>
    <div class="overflow-auto margin-bottom-10">
        <label for="listing_columns_phablet" class="c2 float-left input-line-height"><?php _se('Phablet'); ?></label>
        <input type="number" name="listing_columns_phablet" id="listing_columns_phablet" class="text-input c2" value="<?php echo Settings::get('listing_columns_phablet'); ?>" placeholder="<?php echo Settings::getDefault('listing_columns_phablet'); ?>" pattern="\d*" min="1" max="8" required>
    </div>
    <div class="overflow-auto margin-bottom-10">
        <label for="listing_columns_tablet" class="c2 float-left input-line-height"><?php _se('Tablet'); ?></label>
        <input type="number" name="listing_columns_tablet" id="listing_columns_tablet" class="text-input c2" value="<?php echo Settings::get('listing_columns_tablet'); ?>" placeholder="<?php echo Settings::getDefault('listing_columns_tablet'); ?>" pattern="\d*" min="1" max="8" required>
    </div>
    <div class="overflow-auto margin-bottom-10">
        <label for="listing_columns_laptop" class="c2 float-left input-line-height"><?php _se('Laptop'); ?></label>
        <input type="number" name="listing_columns_laptop" id="listing_columns_laptop" class="text-input c2" value="<?php echo Settings::get('listing_columns_laptop'); ?>" placeholder="<?php echo Settings::getDefault('listing_columns_laptop'); ?>" pattern="\d*" min="1" max="8" required>
    </div>
    <div class="overflow-auto margin-bottom-10">
        <label for="listing_columns_desktop" class="c2 float-left input-line-height"><?php _se('Desktop'); ?></label>
        <input type="number" name="listing_columns_desktop" id="listing_columns_desktop" class="text-input c2" value="<?php echo Settings::get('listing_columns_desktop'); ?>" placeholder="<?php echo Settings::getDefault('listing_columns_desktop'); ?>" pattern="\d*" min="1" max="8" required>
    </div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['listing_columns'] ?? ''; ?></div>
</div>
