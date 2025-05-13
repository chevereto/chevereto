<?php

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('api', 'API'); ?>
<div class="input-label"><i class="fas fa-info-circle"></i> <?php _se('The API enables to programmatically interact with %s.', 'Chevereto'); ?></div>
<div class="input-label">
    <label for="enable_api_user"><?php _se('Enable API %s', _n('user', 'users', 1)); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_api_user" id="enable_api_user" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_api_user')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to allow %s to interact with the API.', _n('user', 'users', 20)); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <label for="enable_api_guest"><?php _se('Enable API %s', _n('guest', 'guests', 1)); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_api_guest" id="enable_api_guest" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_api_guest')); ?>
        </select></div>
        <div class="input-below"><?php _se('Enable this if you want to allow %s to interact with the API.', _n('guest', 'guests', 20)); ?></div>
        <?php personal_mode_warning(); ?>
</div>
<div>
    <?php
        $api = get_base_url('api/1/upload', true);
        $key = Settings::get('api_v1_key');
        $code = <<<COMMAND
        curl --fail-with-body -X POST \
            -H "X-API-Key: $key" \
            -H "Content-Type: multipart/form-data" \
            -F "source=@image.jpeg" \
            $api
        COMMAND;
    ?>
    <div class="margin-bottom-10 margin-top-10">
        <code class="code code--command display-inline-block" data-click="select-all"><?php echo $code; ?></code>
    </div>
    <p><?php _se('Check the %s documentation to learn more.', '<a rel="external" href="https://v4-docs.chevereto.com/developer/api/api-v1.html" target="_blank">API V1</a>'); ?></p>
</div>
<div class="input-label">
    <label for="api_v1_key"><?php _se('Public API key'); ?></label>
    <div class="c12 phablet-c1 position-relative">
        <input type="text" name="api_v1_key" id="api_v1_key" class="text-input" value="<?php echo Settings::get('api_v1_key'); ?>">
        <button type="button" class="input-action" data-action="copy" data-action-target="#api_v1_key"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
    </div>
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['api_v1_key'] ?? ''; ?></div>
    <div class="input-below"><?php _se('This key is for guest usage.'); ?></div>
</div>
