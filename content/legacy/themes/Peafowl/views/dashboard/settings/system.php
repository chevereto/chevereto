<?php

use Chevereto\Config\Config;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('system', _s('System')); ?>
<div class="input-label">
    <label for="enable_automatic_updates_check"><?php _se('Automatic updates check'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_automatic_updates_check" id="enable_automatic_updates_check" class="text-input">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_automatic_updates_check')); ?>
    </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['enable_automatic_updates_check'] ?? ''; ?></div>
    <div class="input-below"><?php _se('When enabled the system will automatically check for new updates.'); ?></div>
</div>
<div class="input-label">
    <label for="update_check_display_notification"><?php _se('Display available updates notification'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="update_check_display_notification" id="update_check_display_notification" class="text-input">
        <?php
                echo get_select_options_html([0 => _s('Disabled'), 1 => _s('Enabled')], Settings::get('update_check_display_notification')); ?>
    </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['update_check_display_notification'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Enable this to show a notice on top warning you about new available system updates.'); ?></div>
</div>
<div class="input-label">
    <label for="dump_update_query"><?php _se('Dump update query'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="dump_update_query" id="dump_update_query" class="text-input">
        <?php
                echo get_select_options_html([0 => _s('Disabled'), 1 => _s('Enabled')], Settings::get('dump_update_query')); ?>
    </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['dump_update_query'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Enable this if you want to dump the update query to run it manually.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="cdn">CDN</label>
    <div class="c5 phablet-c1"><select type="text" name="cdn" id="cdn" class="text-input" data-combo="cdn-combo">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Handler::var('safe_post') ? Handler::var('safe_post')['cdn'] : Settings::get('cdn')); ?>
    </select></div>
</div>
<div id="cdn-combo" class="c9 phablet-c1">
    <div data-combo-value="1" class="switch-combo<?php if (!(Handler::var('safe_post') ? Handler::var('safe_post')['cdn'] : Settings::get('cdn'))) {
                    echo ' soft-hidden';
                } ?>">
        <div class="input-label">
            <label for="cdn_url">CDN URL</label>
            <input type="text" name="cdn_url" id="cdn_url" class="text-input" value="<?php echo Handler::var('safe_post')['cdn_url'] ?? Settings::get('cdn_url'); ?>" placeholder="http://a.cdn.url.com/">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['cdn_url'] ?? ''; ?></div>
        </div>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="website_search"><?php _se('Maintenance'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="maintenance" id="maintenance" class="text-input">
        <?php
                echo get_select_options_html([0 => _s('Disabled'), 1 => _s('Enabled')], Settings::get('maintenance')); ?>
    </select></div>
    <div class="input-below"><?php _se("When enabled the website will show a maintenance message."); ?> <?php _se("This setting doesn't affect administrators."); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="crypt_salt"><?php _se('Crypt salt'); ?></label>
    <div class="c5 phablet-c1"><input type="text" name="crypt_salt" id="crypt_salt" class="text-input" value="<?php echo Settings::get('crypt_salt'); ?>" disabled></div>
    <div class="input-below"><?php _se('This is the salt used to convert numeric ID to alphanumeric. It was generated on install.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="debug_errors"><?php _se('Debug errors'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="debug_errors" id="debug_errors" class="text-input">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('debug_errors')); ?>
    </select></div>
    <div class="input-below"><?php _se('Enable this if you want to debug errors.'); ?> <?php _se('This feature is available only for administrators.'); ?></div>
</div>
<div class="input-label">
    <label for="debug_level"><?php _se('Debug level'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="debug_level" id="debug_level" class="text-input" disabled>
        <?php
                echo get_select_options_html([0 => _s('None'), 1 => _s('Error log'), 2 => _s('Print errors without error log'), 3 => _s('Print and log errors')], Config::system()->debugLevel()); ?>
    </select></div>
    <div class="input-below"><?php _se(
                    'To configure the debug level check the %docs%.',
                    ['%docs%' => '<a rel="external" href="https://v4-docs.chevereto.com/developer/how-to/debug.html" target="_blank">' . _s('documentation') . '</a>']
                ); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <a href="https://xr-docs.chevere.org/" target="_blank"><img alt="XR Debug" width="100" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNTAwIDE1MDAiPgogICAgPGRlZnM+CiAgICAgICAgPGxpbmVhckdyYWRpZW50IGlkPSJjaGV2ZXJlIiB5MT0iNzUwIiB4Mj0iMTUwMCIgeTI9Ijc1MCIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICAgICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiMzNDk4ZGIiLz4KICAgICAgICAgICAgPHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjOGU0NGFkIi8+CiAgICAgICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJYUiIgZGF0YS1uYW1lPSJYUiI+CiAgICAgICAgPHJlY3Qgd2lkdGg9IjE1MDAiIGhlaWdodD0iMTUwMCIgcng9IjM2NS4wOCIgc3R5bGU9ImZpbGw6dXJsKCNjaGV2ZXJlKSIvPgogICAgICAgIDxwYXRoIGQ9Ik03NzYuNzIgNDMzLjI0aDMwMGM5NyAwIDE2NC4wOCAyNS4zOCAyMDYuNjggNjguODkgMzcuMTcgMzYuMjYgNTYuMjEgODUuMjEgNTYuMjEgMTQ3Ljc2djEuODJjMCA5Ny01MS42NyAxNjEuMzUtMTMwLjU0IDE5NC44OWwxNTEuMzkgMjIxLjE5aC0yMDMuMDFsLTEyNy44Mi0xOTIuMThoLTc3LjA1djE5Mi4xOEg3NzYuNzJabTI5MS44OSAzMDQuNThjNTkuODMgMCA5NC4yOC0yOSA5NC4yOC03NS4yNHYtMS44MWMwLTQ5Ljg2LTM2LjI2LTc1LjI0LTk1LjE4LTc1LjI0SDk1Mi41OHYxNTIuMjlaIiBzdHlsZT0ib3BhY2l0eTouOTtmaWxsOiNlY2YwZjEiLz4KICAgICAgICA8cGF0aCBkPSJNMzU0LjgyIDc0NS4wNyA1NzEuOTMgNDMzaDE5NS41OUw1NjEuMTcgNzQzLjI2IDc3Ni40OSAxMDY4SDU3Ni40MloiIHN0eWxlPSJmaWxsOiNlY2YwZjE7IG9wYWNpdHk6Ljc1Ii8+CiAgICAgICAgPHBhdGggZD0iTTM1NC44MiA3NDUuMDcgMTQ4LjQ3IDQzM2gyMDAuMDdsMjEyLjYzIDMxMC4yNkwzMzUuMDggMTA2OEgxMzkuNDlaIiBzdHlsZT0iZmlsbDojZWNmMGYxOyIvPgogICAgPC9nPgo8L3N2Zz4=" /></a>
</div>
<div class="input-label">
    <label for="enable_xr"><?php _se('Enable %service%', ['%service%' => 'XR Debug']); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_xr" id="enable_xr" class="text-input" data-combo="xr-enable-combo">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_xr')); ?>
    </select></div>
    <div class="input-below"><?php _se('Enable this if you want to send debug messages to %xr%.', ['%xr%' => '<a href="https://xr-docs.chevere.org/" target="_blank" rel="external">chevere/xr</a>']); ?></div>
</div>
<div id="xr-enable-combo">
    <div data-combo-value="1" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['enable_xr'] == 0 : !Settings::get('enable_xr'))) {
                    echo ' soft-hidden';
                } ?>">
        <div class="input-label">
            <label for="xr_host"><?php _se('%s Host', 'XR Debug'); ?></label>
            <div class="c5 phablet-c1">
                <input type="text" name="xr_host" id="xr_host" class="text-input" value="<?php echo Settings::get('xr_host'); ?>" placeholder="localhost">
                <datalist id="xr_host">
                    <option value="localhost">
                    <option value="host.docker.internal">
                </datalist>
            </div>
        </div>
        <div class="input-label">
            <label for="xr_port"><?php _se('%s Port', 'XR Debug'); ?></label>
            <div class="c3 phablet-c1"><input type="number" name="xr_port" id="xr_port" class="text-input" value="<?php echo Settings::get('xr_port'); ?>" placeholder="27420"></div>
        </div>
        <div class="input-label">
            <label for="xr_key"><?php _se('%s Key', 'XR Debug'); ?></label>
            <div class="c14 phablet-c1"><textarea name="xr_key" id="xr_key" class="r4 resize-none" placeholder="<?php _se('Private key'); ?>"><?php echo Settings::get('xr_key'); ?></textarea></div>
        </div>
    </div>
</div>
