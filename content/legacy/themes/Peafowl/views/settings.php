<?php

use function Chevereto\Legacy\G\get_input_auth_token;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
require_theme_header(); ?>
<div class="top-sub-bar follow-scroll margin-bottom-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
            <h1 class="header-title"><strong><span class="header-icon fas fa-user-cog"></span><span class="phone-hide margin-left-5"><?php _se('Settings'); ?></span></strong>
            </h1>
    	    <?php require_theme_file("snippets/tabs"); ?>
        </div>
    </div>
</div>
<?php
if (Handler::cond('settings_account')
    && (Handler::cond('dashboard_user') || Handler::cond('content_manager'))
    && Handler::var('user')['registration_ip']
) { ?>
<div data-modal="modal-add-ip_ban" class="hidden" data-submit-fn="CHV.fn.ip_ban.add.submit" data-before-fn="CHV.fn.ip_ban.add.before" data-ajax-deferred="CHV.fn.ip_ban.add.complete">
    <span class="modal-box-title"><i class="fas fa-ban"></i> <?php _se('Add IP ban'); ?></span>
    <div class="modal-form">
        <?php require_theme_file('snippets/form_ip_ban_edit'); ?>
    </div>
</div>
<?php } ?>
<div class="content-width">
    <div class="form-content">
        <form data-content="main-form" class="overflow-auto" method="post" data-type="<?php echo Handler::var('setting'); ?>" data-action="validate">
            <?php echo get_input_auth_token(); ?>
            <?php
            if (Handler::cond('settings_account')) {
                require 'settings/account.php';
            }
            if (Handler::cond('settings_powered')) {
                require 'settings/powered.php';
            }
            if (Handler::cond('settings_api')) {
                require 'settings/api.php';
            }
            if (Handler::cond('settings_password')) {
                require 'settings/password.php';
            }
            if (Handler::cond('settings_profile')) {
                require 'settings/profile.php';
            }
            if (Handler::cond('settings_connections')) {
                require 'settings/connections.php';
            }
            if (Handler::cond('settings_security')) {
                require 'settings/security.php';
            }
            if (Handler::cond('settings_homepage')) {
                require 'settings/homepage.php';
            } ?>
            <?php if (Handler::cond('captcha_needed') && Handler::var('captcha_html') !== null) {
                ?>
                <div class="input-label">
                    <label for="recaptcha_response_field">CAPTCHA</label>
                    <?php echo Handler::var('captcha_html'); ?>
                </div>
            <?php
            }
            if (!Handler::cond('settings_connections')
                && !Handler::cond('settings_powered')
                && !Handler::cond('settings_api')
                && !Handler::cond('settings_security')
            ) {
                ?>
                <div class="btn-container btn-container--fixed">
                    <div class="c24 center-box text-align-center">
                        <button class="btn btn-input accent" type="submit" title="Ctrl/Cmd + Enter"><span class="fa fa-check-circle btn-icon"></span><span class="btn-text"><?php _se('Save changes'); ?></span></button>
                    </div>
                </div>
            <?php
            } ?>
        </form>
    </div>
</div>
<?php if (Handler::var('post') && Handler::cond('error')) { ?>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
    PF.fn.growl.call("<?php echo Handler::var('error_message') ?? _s('Check the errors to proceed.'); ?>");
});
</script>
<?php }

require_theme_footer();
