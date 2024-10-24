<?php

use Chevereto\Config\Config;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('upload-plugin', _s('Upload plugin')); ?>
<div class="input-label">
    <label for="enable_plugin_route"><?php _se('Plugin route'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_plugin_route" id="enable_plugin_route" class="text-input">
            <?php
                        echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_plugin_route')); ?>
        </select></div>
    <div class="input-below"><?php _se("Enable this to display plugin instructions at %u. A link to these instructions will be added to the %s menu.", [
                                                            '%u' => Config::host()->hostnamePath() . 'plugin',
                                                            '%s' => '“' . _s('About') . '”',
                                                        ]); ?> <?php _se("This setting doesn't affect administrators."); ?></div>
</div>
<?php if(env()['CHEVERETO_ENABLE_PUP_CUSTOM_URL'] === '1') { ?>
<div class="input-label">
    <label for="sdk_pup_url">PUP SDK URL</label>
    <div class="c9 phablet-c1"><input type="text" name="sdk_pup_url" id="sdk_pup_url" class="text-input" value="<?php echo Settings::get('sdk_pup_url'); ?>" placeholder="<?php _se('Empty'); ?>"></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['sdk_pup_url'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Use this to set a custom URL for %p. Please note that you need to manually replicate %s in this URL.', ['%p' => 'PUP SDK', '%s' => Config::host()->hostnamePath() . 'sdk/pup.js']); ?></div>
</div>
<?php } ?>
