<?php

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('homepage', _s('Homepage')); ?>
<div class="input-label">
    <label for="homepage_style"><?php _se('Style'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="homepage_style" id="homepage_style" class="text-input" data-combo="home-style-combo">
            <?php
            echo get_select_options_html([
                'landing' => _s('Landing page'),
                'split' => _s('Split landing + images'),
                'route_explore' => _s('Route %s', _s('explore')),
                'route_upload' => _s('Route %s', _s('upload')),
            ], Settings::get('homepage_style')); ?>
        </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_style'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Select the homepage style. To customize it further edit app/themes/%s/views/index.php', Settings::get('theme')); ?></div>
</div>
<div id="home-style-combo">
    <div data-combo-value="landing split" class="switch-combo<?php if (!in_array((Handler::var('safe_post') ? Handler::var('safe_post')['homepage_style'] : Settings::get('homepage_style')), ['split', 'landing'])) {
                echo ' soft-hidden';
            } ?>">
        <?php
        foreach (Settings::get('homepage_cover_images') ?? [] as $k => $v) {
            $cover_index = $k + 1;
            $cover_label = 'homepage_cover_image_' . $k;
            $coverName = _s('Cover image') . ' (' . $cover_index . ')'; ?>
            <div class="input-label">
                <label for="<?php echo $cover_label; ?>"><?php echo $coverName; ?></label>
                <div class="transparent-canvas dark margin-bottom-10" style="max-width: 200px;"><img class="display-block" width="100%" src="<?php echo $v['url']; ?>"></div>
                <?php if (count(Settings::get('homepage_cover_images')) > 1) {
                ?>
                    <div class="margin-top-10 margin-bottom-10">
                        <a class="btn btn-small default" data-confirm="<?php _se("Do you really want to delete this image? This can't be undone."); ?>" href="<?php echo get_base_url('dashboard/settings/homepage?action=delete-cover&cover=' . $cover_index); ?>"><i class="fas fa-trash-alt margin-right-5"></i><?php _se('Delete %s', $coverName); ?></a>
                    </div>
                <?php
            } ?>
                <div class="c5 phablet-c1">
                    <input id="<?php echo $cover_label; ?>" name="<?php echo $cover_label; ?>" type="file" accept="image/*">
                </div>
                <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_cover_image_' . $k] ?? ''; ?></div>
            </div>
        <?php
        } ?>
        <div class="input-label">
            <label for="homepage_cover_image_add"><?php _se('Add new cover image'); ?></label>
            <div class="c5 phablet-c1">
                <input id="homepage_cover_image_add" name="homepage_cover_image_add" type="file" accept="image/*">
            </div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_cover_image_add'] ?? ''; ?></div>
        </div>
        <hr class="line-separator">
        <div class="input-label">
            <label for="homepage_title_html"><?php _se('Title'); ?></label>
            <div class="c12 phablet-c1"><textarea type="text" name="homepage_title_html" id="homepage_title_html" class="text-input r2 resize-vertical" placeholder="<?php echo safe_html(_s('This will be added inside the homepage %s tag. Leave it blank to use the default contents.', '<h1>')); ?>"><?php echo Settings::get('homepage_title_html'); ?></textarea></div>
        </div>
        <div class="input-label">
            <label for="homepage_paragraph_html"><?php _se('Paragraph'); ?></label>
            <div class="c12 phablet-c1"><textarea type="text" name="homepage_paragraph_html" id="homepage_paragraph_html" class="text-input r2 resize-vertical" placeholder="<?php echo safe_html(_s('This will be added inside the homepage %s tag. Leave it blank to use the default contents.', '<p>')); ?>"><?php echo Settings::get('homepage_paragraph_html'); ?></textarea></div>
        </div>
        <hr class="line-separator">
        <div class="input-label">
            <label for="homepage_cta_color"><?php _se('Call to action button color'); ?></label>
            <div class="c5 phablet-c1"><select type="text" name="homepage_cta_color" id="homepage_cta_color" class="text-input">
                    <?php
                    echo get_select_options_html(
                    [
                        'blue' => _s('Blue'),
                        'green' => _s('Green'),
                        'orange' => _s('Orange'),
                        'red' => _s('Red'),
                        'grey' => _s('Grey'),
                        'black' => _s('Black'),
                        'white' => _s('White'),
                        'default' => _s('Default'),
                    ],
                    Handler::var('safe_post')
                            ? Handler::var('safe_post')['homepage_cta_color']
                            : Settings::get('homepage_cta_color')
                ); ?>
                </select></div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['homepage_cta_color'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Color of the homepage call to action button.'); ?></div>
        </div>
        <div class="input-label">
            <label for="homepage_cta_outline"><?php _se('Call to action outline style button'); ?></label>
            <div class="c5 phablet-c1"><select type="text" name="homepage_cta_outline" id="homepage_cta_outline" class="text-input">
                    <?php
                    echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('homepage_cta_outline')); ?>
                </select></div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['homepage_cta_outline'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Enable this to use outline style for the homepage call to action button.'); ?></div>
        </div>
        <div class="input-label">
            <label for="homepage_cta_fn"><?php _se('Call to action functionality'); ?></label>
            <div class="c5 phablet-c1"><select type="text" name="homepage_cta_fn" id="homepage_cta_fn" class="text-input" data-combo="cta-fn-combo">
                    <?php
                    echo get_select_options_html([
                        'cta-upload' => _s('Trigger uploader'),
                        'cta-link' => _s('Open URL'),
                    ], Settings::get('homepage_cta_fn')); ?>
                </select></div>
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_cta_fn'] ?? ''; ?></div>
        </div>
        <div id="cta-fn-combo">
            <div data-combo-value="cta-link" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['homepage_cta_fn'] : Settings::get('homepage_cta_fn')) !== 'cta-link') {
                        echo ' soft-hidden';
                    } ?>">
                <div class="input-label">
                    <label for="homepage_cta_fn_extra"><?php _se('Call to action URL'); ?></label>
                    <div class="c9 phablet-c1"><input type="text" name="homepage_cta_fn_extra" id="homepage_cta_fn_extra" class="text-input" value="<?php echo Settings::get('homepage_cta_fn_extra'); ?>" placeholder="<?php _se('Enter an absolute or relative URL'); ?>" <?php echo ((Handler::var('safe_post') ? Handler::var('safe_post')['homepage_cta_fn'] : Settings::get('homepage_cta_fn')) !== 'cta-link') ? 'data-required' : 'required'; ?>></div>
                    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_cta_fn_extra'] ?? ''; ?></div>
                    <div class="input-below"><?php _se('A relative URL like %r will be mapped to %l', ['%r' => 'page/welcome', '%l' => get_base_url('page/welcome')]); ?></div>
                </div>
            </div>
        </div>
        <div class="input-label">
            <label for="homepage_cta_html"><?php _se('Call to action HTML'); ?></label>
            <div class="c12 phablet-c1"><textarea type="text" name="homepage_cta_html" id="homepage_cta_html" class="text-input r2 resize-vertical" placeholder="<?php echo safe_html(_s('This will be added inside the call to action <a> tag. Leave it blank to use the default contents.')); ?>"><?php echo Settings::get('homepage_cta_html'); ?></textarea></div>
        </div>
    </div>
    <div data-combo-value="split" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['homepage_style'] : Settings::get('homepage_style')) !== 'split') {
                        echo ' soft-hidden';
                    } ?>">
        <div class="input-label">
            <label for="homepage_uids"><?php _se('User IDs'); ?></label>
            <div class="c4"><input type="text" name="homepage_uids" id="homepage_uids" class="text-input" value="<?php echo Settings::get('homepage_uids'); ?>" placeholder="<?php _se('Empty'); ?>" rel="tooltip" title="<?php _se('Your user id is: %s', Login::getUser()['id']); ?>" data-tipTip="right"></div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['homepage_uids'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Comma-separated list of target user IDs (integers) to show most recent images on homepage. Leave it empty to display trending images.'); ?></div>
        </div>
    </div>
</div>
