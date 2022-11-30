<?php
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\random_string;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\get_system_image_url;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('logo', _s('Logo')); ?>
<div class="input-label">
    <label for="logo_type"><?php _se('Logo'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="logo_type" id="logo_type" class="text-input" data-combo="logo-combo">
            <?php
            echo get_select_options_html(
    ['vector' => _s('Vector'), 'image' => _s('Image'), 'text' => _s('Text')],
    Handler::var('safe_post') ? Handler::var('safe_post')['logo_type'] : Settings::get('logo_type')
); ?>
        </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['logo_type'] ?? ''; ?></div>
    <div class="input-below clear-both"><?php _se('Text option uses the website name as logo.'); ?></div>
</div>
<div id="logo-combo">
<?php
$logoType = Handler::var('safe_post') && (bool) env()['CHEVERETO_ENABLE_LOGO']
    ? Handler::var('safe_post')['logo_type']
    : Settings::get('logo_type');
$logoComboVisibility = function (string ...$try) use ($logoType): string {
    return !in_array($logoType, $try)
        ? ' soft-hidden'
        : '';
}
?>
    <div data-combo-value="vector" class="input-label switch-combo<?php echo $logoComboVisibility('vector'); ?>">
        <label for="logo_vector"><?php _se('Logo vector'); ?></label>
        <div class="transparent-canvas dark margin-bottom-10" style="max-width: 200px;"><img class="display-block" width="100%" src="<?php echo get_system_image_url(Settings::get('logo_vector')) . '?' . random_string(8); ?>"></div>
        <div class="c5 phablet-c1">
            <input id="logo_vector" name="logo_vector" type="file" accept="image/svg">
        </div>
        <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['logo_vector'] ?? ''; ?></div>
        <div class="input-below"><?php _se('Vector version or your website logo in SVG format.'); ?></div>
    </div>
    <div data-combo-value="image" class="input-label switch-combo<?php echo $logoComboVisibility('image'); ?>">
        <label for="logo_image"><?php _se('Logo image'); ?></label>
        <div class="transparent-canvas dark margin-bottom-10" style="max-width: 200px;"><img class="display-block" width="100%" src="<?php echo get_system_image_url(Settings::get('logo_image')) . '?' . random_string(8); ?>"></div>
        <div class="c5 phablet-c1">
            <input id="logo_image" name="logo_image" type="file" accept="image/*">
        </div>
        <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['logo_image'] ?? ''; ?></div>
        <div class="input-below"><?php _se('Bitmap version or your website logo. PNG format is recommended.'); ?></div>
    </div>
    <div data-combo-value="vector image" class="input-label switch-combo<?php echo $logoComboVisibility('vector', 'image'); ?>">
        <label for="theme_logo_height"><?php _se('Logo height'); ?></label>
        <div class="c4"><input type="number" min="0" pattern="\d+" name="theme_logo_height" id="theme_logo_height" class="text-input" value="<?php echo Settings::get('theme_logo_height'); ?>" placeholder="<?php _se('No value'); ?>"></div>
        <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['theme_logo_height'] ?? ''; ?></div>
        <div class="input-below"><?php _se('Use this to set the logo height if needed.'); ?></div>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="favicon_image"><?php _se('Favicon image'); ?></label>
    <div class="transparent-canvas dark margin-bottom-10" style="max-width: 100px;"><img class="display-block" width="100%" src="<?php echo get_system_image_url(Settings::get('favicon_image')) . '?' . random_string(8); ?>"></div>
    <div class="c5 phablet-c1">
        <input id="favicon_image" name="favicon_image" type="file" accept="image/*">
    </div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['favicon_image'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Favicon image. Image must have same width and height.'); ?></div>
</div>
