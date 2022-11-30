<?php

use Chevereto\Legacy\Classes\Palettes;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('theme', _s('Theme')); ?>
<div class="input-label">
    <label for="theme"><?php _se('Theme'); ?></label>
    <?php
    $themes = [];
foreach (scandir(PATH_PUBLIC_CONTENT_LEGACY_THEMES) as $v) {
    if (is_dir(PATH_PUBLIC_CONTENT_LEGACY_THEMES . DIRECTORY_SEPARATOR . $v) and !in_array($v, ['.', '..'])) {
        $themes[$v] = $v;
    }
} ?>
    <div class="c5 phablet-c1">
        <select type="text" name="theme" id="theme" class="text-input">
            <?php
            echo get_select_options_html($themes, Settings::get('theme')); ?>
        </select>
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
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_download_button')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show the image download button.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_image_right_click"><?php _se('Enable right click on image'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_image_right_click" id="theme_image_right_click" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_image_right_click')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to allow right click on image viewer page.'); ?></div>
</div>
<div class="input-label">
    <label for="theme_show_exif_data"><?php _se('Enable show Exif data'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_show_exif_data" id="theme_show_exif_data" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_show_exif_data')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show image Exif data.'); ?></div>
</div>
<div class="input-label">
    <label for="image_first_tab"><?php _se('%s first tab', _s('Image')); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="image_first_tab" id="image_first_tab" class="text-input">
            <?php
            echo get_select_options_html(
    [
                    'embeds' => _s('Embed codes'),
                    'about' => _s('About'),
                    'info' => _s('Info'),
                ],
    Settings::get('image_first_tab')
);
            ?>
        </select></div>
    <div class="input-below"><?php _se('Determine the first tab on %s page.', _s('image')); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="theme_show_social_share"><?php _se('Enable social share'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_show_social_share" id="theme_show_social_share" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_show_social_share')); ?>
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
                'none' => _s('Disabled')
                ], Settings::get('theme_show_embed_content_for')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show embed codes for the content.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="theme_nsfw_upload_checkbox"><?php _se('Not safe content checkbox in uploader'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="theme_nsfw_upload_checkbox" id="theme_nsfw_upload_checkbox" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('theme_nsfw_upload_checkbox')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show a checkbox to indicate not safe content upload.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="comments_api"><?php _se('Comments API'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="comments_api" id="comments_api" class="text-input" data-combo="comments_api-combo">
        <?php
                echo get_select_options_html([
                    'disqus' => 'Disqus',
                    'js' => 'JavaScript/HTML',
                ], Handler::var('safe_post') ? Handler::var('safe_post')['comments_api'] : Settings::get('comments_api')); ?>
    </select></div>
    <div class="input-below"><?php _se('Disqus API works with %s.', '<a rel="external" href="https://help.disqus.com/customer/portal/articles/236206" target="_blank">Single Sign-On</a> (SSO)'); ?></div>
</div>
<div id="comments_api-combo">
    <div data-combo-value="disqus" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['comments_api'] : Settings::get('comments_api')) !== 'disqus') {
                    echo ' soft-hidden';
                } ?>">
        <div class="c9 phablet-c1">
            <div class="input-label">
                <label for="disqus_shortname"><?php _se('Disqus shortname'); ?></label>
                <input type="text" name="disqus_shortname" id="disqus_shortname" class="text-input" value="<?php echo Handler::var('safe_post')['disqus_shortname'] ?? Settings::get('disqus_shortname'); ?>">
                <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['disqus_shortname'] ?? ''; ?></div>
            </div>
            <div class="input-label">
                <label for="disqus_secret_key"><?php _se('%s secret key', 'Disqus'); ?></label>
                <input type="text" name="disqus_secret_key" id="disqus_secret_key" class="text-input" value="<?php echo Handler::var('safe_post')['disqus_secret_key'] ?? Settings::get('disqus_secret_key'); ?>">
                <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['disqus_secret_key'] ?? ''; ?></div>
            </div>
            <div class="input-label">
                <label for="disqus_public_key"><?php _se('%s public key', 'Disqus'); ?></label>
                <input type="text" name="disqus_public_key" id="disqus_public_key" class="text-input" value="<?php echo Handler::var('safe_post')['disqus_public_key'] ?? Settings::get('disqus_public_key'); ?>">
                <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['disqus_public_key'] ?? ''; ?></div>
            </div>
        </div>
    </div>
    <div data-combo-value="js" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['comments_api'] : Settings::get('comments_api')) !== 'js') {
                    echo ' soft-hidden';
                } ?>">
        <div class="input-label">
            <label for="comment_code"><?php _se('Comment code'); ?></label>
            <div class="c12 phablet-c1"><textarea type="text" name="comment_code" id="comment_code" class="text-input r4" value="" placeholder="<?php _se('Disqus, Facebook or anything you want. It will be used in image view.'); ?>"><?php echo Settings::get('comment_code'); ?></textarea></div>
        </div>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="analytics_code"><?php _se('Analytics code'); ?></label>
    <div class="c12 phablet-c1"><textarea type="text" name="analytics_code" id="analytics_code" class="text-input r4" value="" placeholder="<?php _se('Google Analytics or anything you want. It will be added to the theme footer.'); ?>"><?php echo Settings::get('analytics_code'); ?></textarea></div>
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
