<?php

use function Chevereto\Legacy\G\get_input_auth_token;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
include_theme_header(); ?>
<div class="top-sub-bar follow-scroll margin-bottom-5 margin-top-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
            <h1 class="header-title"><strong><span class="header-icon fas fa-user-cog"></span><span class="phone-hide margin-left-5"><?php _se('Settings'); ?></span></strong>
            </h1>
    	    <?php include_theme_file("snippets/tabs"); ?>
        </div>
    </div>
</div>

<div class="content-width margin-top-20">
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
<?php if (Handler::var('post')) {
                if (Handler::cond('changed')) { ?>
	<script>
		$(function() {
			PF.fn.growl.expirable("<?php echo Handler::var('changed_message') ?? _s('Changes have been saved.'); ?>");
		});
	</script>
<?php
                }
                if (Handler::cond('error')) { ?>
	<script>
		$(function() {
			PF.fn.growl.call("<?php echo Handler::var('error_message') ?? _s('Check the errors to proceed.'); ?>");
		});
	</script>
<?php }
            }
include_theme_footer(); ?>
