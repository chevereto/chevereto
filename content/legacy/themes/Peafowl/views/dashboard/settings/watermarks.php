<?php
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\random_string;
use function Chevereto\Legacy\get_checkbox_html;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\get_system_image_url;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('watermarks', _s('Watermarks')); ?>
<div class="input-label">
    <label for="watermark_enable"><?php _se('Watermarks'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="watermark_enable" id="watermark_enable" class="text-input" data-combo="watermark-combo">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable'] : Settings::get('watermark_enable')); ?>
        </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['watermark_enable'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Enable this to put a logo or anything you want in image uploads.'); ?></div>
</div>
<div id="watermark-combo">
    <div data-combo-value="1" class="switch-combo phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable'] : Settings::get('watermark_enable')) != 1) {
                echo ' soft-hidden';
            } ?>">
        <div class="input-label">
            <label for="watermark_checkboxes"><?php _se('Watermark user toggles'); ?></label>
            <?php echo get_checkbox_html([
                'name' => 'watermark_enable_guest',
                'label' => _s('Enable watermark on guest uploads'),
                'checked' => ((bool) (Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable_guest'] : Settings::get('watermark_enable_guest'))),
            ]); ?>
            <?php echo get_checkbox_html([
                'name' => 'watermark_enable_user',
                'label' => _s('Enable watermark on user uploads'),
                'checked' => ((bool) (Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable_user'] : Settings::get('watermark_enable_user'))),
            ]); ?>
            <?php echo get_checkbox_html([
                'name' => 'watermark_enable_admin',
                'label' => _s('Enable watermark on admin uploads'),
                'checked' => ((bool) (Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable_admin'] : Settings::get('watermark_enable_admin'))),
            ]); ?>
        </div>
        <div class="input-label">
            <label for="watermark_checkboxes"><?php _se('Watermark file toggles'); ?></label>
            <?php echo get_checkbox_html([
                'name' => 'watermark_enable_file_gif',
                'label' => _s('Enable watermark on GIF image uploads'),
                'checked' => ((bool) (Handler::var('safe_post') ? Handler::var('safe_post')['watermark_enable_file_gif'] : Settings::get('watermark_enable_file_gif'))),
            ]); ?>
            <p class="highlight padding-5 display-inline-block"><i class="fas fa-exclamation-triangle"></i> <?php _se("Animated images won't be watermarked."); ?></p>
        </div>
        <div class="input-label">
            <label for="watermark_target_min_width" class="display-block-forced"><?php _se('Minimum image size needed to apply watermark'); ?></label>
            <div class="c5 overflow-auto clear-both">
                <div class="c2 float-left">
                    <input type="number" min="0" pattern="\d+" name="watermark_target_min_width" id="watermark_target_min_width" class="text-input" value="<?php echo Handler::var('safe_post')['watermark_target_min_width'] ?? Settings::get('watermark_target_min_width'); ?>" placeholder="<?php echo  Settings::getDefault('watermark_target_min_width'); ?>" rel="tooltip" data-tiptip="top" title="<?php _se('Width'); ?>" required>
                </div>
                <div class="c2 float-left margin-left-10">
                    <input type="number" min="0" pattern="\d+" name="watermark_target_min_height" id="watermark_target_min_height" class="text-input" value="<?php echo Handler::var('safe_post')['watermark_target_min_height'] ?? Settings::get('watermark_target_min_height'); ?>" placeholder="<?php echo  Settings::getDefault('watermark_target_min_height'); ?>" rel="tooltip" data-tiptip="top" title="<?php _se('Height'); ?>" required>
                </div>
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_target_min_width'] ?? ''; ?></div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_target_min_height'] ?? ''; ?></div>
            <div class="input-below"><?php _se("Images smaller than this won't be watermarked. Use zero (0) to don't set a minimum image size limit."); ?></div>
        </div>
        <div class="input-label">
            <?php Image::watermarkFromDb(); ?>
            <label for="watermark_image"><?php _se('Watermark image'); ?></label>
            <div class="transparent-canvas dark margin-bottom-10" style="max-width: 200px;"><img class="display-block" width="100%" src="<?php echo get_system_image_url(Settings::get('watermark_image')) . '?' . random_string(8); ?>"></div>
            <div class="c5 phablet-c1">
                <input id="watermark_image" name="watermark_image" type="file" accept="image/png">
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_image'] ?? ''; ?></div>
            <div class="input-below">PNG - <?php _se('Max size %s.', '64KB'); ?></div>
        </div>
        <div class="input-label">
            <label for="watermark_position"><?php _se('Watermark position'); ?></label>
            <div class="c5 phablet-c1"><select type="text" name="watermark_position" id="watermark_position" class="text-input">
                    <?php
                    echo get_select_options_html(
                [
                            'left top' => _s('left top'),
                            'left center' => _s('left center'),
                            'left bottom' => _s('left bottom'),
                            'center top' => _s('center top'),
                            'center center' => _s('center center'),
                            'center bottom' => _s('center bottom'),
                            'right top' => _s('right top'),
                            'right center' => _s('right center'),
                            'right bottom' => _s('right bottom'),
                        ],
                Handler::var('safe_post')['watermark_position']
                            ?? Settings::get('watermark_position')
            ); ?>
                </select></div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['watermark_position'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Relative position of the watermark image. First horizontal align then vertical align.'); ?></div>
        </div>
        <div class="input-label">
            <label for="watermark_percentage"><?php _se('Watermark percentage'); ?></label>
            <div class="c2">
                <input type="number" min="1" max="100" pattern="\d+" name="watermark_percentage" id="watermark_percentage" class="text-input" value="<?php echo Handler::var('safe_post')['watermark_percentage'] ?? Settings::get('watermark_percentage'); ?>" placeholder="<?php echo Settings::getDefault('watermark_percentage'); ?>" required>
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_percentage'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Watermark percentual size relative to the target image area. Values 1 to 100.'); ?></div>
        </div>
        <div class="input-label">
            <label for="watermark_margin"><?php _se('Watermark margin'); ?></label>
            <div class="c2">
                <input type="number" min="0" pattern="\d+" name="watermark_margin" id="watermark_margin" class="text-input" value="<?php echo Handler::var('safe_post')['watermark_margin'] ?? Settings::get('watermark_margin'); ?>" placeholder="<?php echo Settings::getDefault('watermark_margin'); ?>" required>
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_margin'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Margin from the border of the image to the watermark image.'); ?></div>
        </div>
        <div class="input-label">
            <label for="watermark_opacity"><?php _se('Watermark opacity'); ?></label>
            <div class="c2">
                <input type="number" min="1" max="100" pattern="\d+" name="watermark_opacity" id="watermark_opacity" class="text-input" value="<?php echo Handler::var('safe_post')['watermark_opacity'] ?? Settings::get('watermark_opacity'); ?>" placeholder="<?php echo Settings::getDefault('watermark_opacity'); ?>" required>
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['watermark_opacity'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Opacity of the watermark in the final watermarked image. Values 0 to 100.'); ?></div>
        </div>
    </div>
</div>
