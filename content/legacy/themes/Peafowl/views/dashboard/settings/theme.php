<?php

use Chevereto\Legacy\Classes\Fonts;
use Chevereto\Legacy\Classes\Palettes;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (! defined('ACCESS') || ! ACCESS) {
    exit('This file cannot be directly accessed.');
}
echo read_the_docs_settings('theme', _s('Theme')); ?>
<div class="input-label">
    <label for="theme"><?php _se('Theme'); ?></label>
    <?php
    $themes = [];
foreach (scandir(PATH_PUBLIC_CONTENT_LEGACY_THEMES) as $v) {
    if (is_dir(PATH_PUBLIC_CONTENT_LEGACY_THEMES . DIRECTORY_SEPARATOR . $v)
        && ! in_array($v, ['.', '..'], true)
    ) {
        $themes[$v] = $v;
    }
} ?>
    <div class="c5 phablet-c1">
        <select type="text" name="theme" id="theme" class="text-input">
            <?php
            echo get_select_options_html($themes, Settings::get('theme')); ?>
        </select>
        <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['theme'] ?? ''; ?></div>
    </div>
</div>
<div class="input-label">
<?php
/** @var Palettes $palettes */
$palettes = Handler::var('palettes');
$palettesOptions = [];
foreach (array_keys($palettes->get()) as $id) {
    $palettesOptions[strval($id)] = $palettes->getName($id);
}
?>
    <label for="theme_palette"><?php _se('Default %s', _s('palette')); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_palette" id="theme_palette" class="text-input">
            <?php
            echo get_select_options_html($palettesOptions, Handler::var('safe_post') ? Handler::var('safe_post')['theme_palette'] : Settings::get('theme_palette')); ?>
        </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['theme_palette'] ?? ''; ?></div>
</div>
<div class="input-label">
<?php
/** @var Fonts $fonts */
$fonts = Handler::var('fonts');
$fontsOptions = [];
foreach (array_keys($fonts->get()) as $id) {
    $fontsOptions[strval($id)] = $fonts->getName($id);
}
?>
    <label for="theme_font"><?php _se('Default %s', _s('font')); ?></label>
    <div class="c8 phablet-c1"><select type="text" name="theme_font" id="theme_font" class="text-input">
            <?php
            echo get_select_options_html($fontsOptions, Handler::var('safe_post') ? Handler::var('safe_post')['theme_font'] : Settings::get('theme_font')); ?>
        </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['theme_font'] ?? ''; ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="image_load_max_filesize_mb"><?php _se('Image load max. filesize'); ?> (MB)</label>
    <div class="c2"><input type="number" min="0.1" step="0.1" pattern="\d+" name="image_load_max_filesize_mb" id="image_load_max_filesize_mb" class="text-input" value="<?php echo Handler::var('safe_post')['image_load_max_filesize_mb'] ?? Settings::get('image_load_max_filesize_mb'); ?>" placeholder="MB"></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['image_load_max_filesize_mb'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Images greater than this size will show a button to load full resolution image.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_download_button"><?php _se('Enable download button'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_download_button" id="theme_download_button" class="text-input">
            <?php
            echo get_select_options_html([
                1 => _s('Enabled'),
                0 => _s('Disabled'),
            ], Settings::get('theme_download_button')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show the image download button.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_image_right_click"><?php _se('Enable right click on image'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_image_right_click" id="theme_image_right_click" class="text-input">
            <?php
            echo get_select_options_html([
                1 => _s('Enabled'),
                0 => _s('Disabled'),
            ], Settings::get('theme_image_right_click')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to allow right click on image viewer page.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_show_exif_data"><?php _se('Enable show Exif data'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_show_exif_data" id="theme_show_exif_data" class="text-input">
            <?php
            echo get_select_options_html([
                1 => _s('Enabled'),
                0 => _s('Disabled'),
            ], Settings::get('theme_show_exif_data')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show image Exif data.'); ?></div>
</div>
<div class="input-label">
    <label for="image_first_tab"><?php _se('%s first tab', _n('Image', 'Images', 1)); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="image_first_tab" id="image_first_tab" class="text-input">
            <?php
            echo get_select_options_html(
                [
                    'embeds' => _s('Embed codes'),
                    'about' => _s('About'),
                    'comments' => _s('Comments'),
                    'info' => _s('Info'),
                ],
                Settings::get('image_first_tab')
            );
?>
        </select></div>
    <div class="input-below"><?php _se('Determine the first tab on %s page.', _n('image', 'images', 1)); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="theme_show_social_share"><?php _se('Enable social share'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_show_social_share" id="theme_show_social_share" class="text-input">
            <?php
echo get_select_options_html([
    1 => _s('Enabled'),
    0 => _s('Disabled'),
], Settings::get('theme_show_social_share')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show social network buttons to share content.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_show_embed_content_for"><?php _se('Enable embed codes (content)'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_show_embed_content_for" id="theme_show_embed_content_for" class="text-input">
            <?php
echo get_select_options_html([
    'all' => _s('Everybody'),
    'users' => _s('Users only'),
    'none' => _s('Disabled'),
], Settings::get('theme_show_embed_content_for')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show embed codes for the content.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="theme_custom_css_code"><?php _se('Custom CSS code'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="theme_custom_css_code" id="theme_custom_css_code" class="text-input r4" placeholder="<?php _se('Put your custom CSS code here. It will be placed as <style> just before the closing </head> tag.'); ?>"><?php echo Settings::get('theme_custom_css_code'); ?></textarea></div>
</div>
<div class="input-label">
    <label for="theme_custom_js_code"><?php _se('Custom JS code'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="theme_custom_js_code" id="theme_custom_js_code" class="text-input r4" placeholder="<?php _se('Put your custom JS code here. It will be placed as <script> just before the closing </head> tag.'); ?>"><?php echo Settings::get('theme_custom_js_code'); ?></textarea></div>
    <div class="input-below"><?php _se('Do not use %s markup here. This is for plain JS code, not for HTML script tags. If you use script tags here you will break your website.', '&lt;script&gt;'); ?></div>
</div>
