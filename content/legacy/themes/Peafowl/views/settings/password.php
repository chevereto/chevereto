<?php

use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (Login::hasPassword(Handler::var('user')['id'])) { ?>
    <div class="c12 phablet-c1">
        <?php
                if (!Handler::cond('dashboard_user')) {
                    ?>
            <div class="input-label input-password">
                <label for="current-password"><?php _se('Current password'); ?></label>
                <input autocomplete="current-password" type="password" name="current-password" id="current-password" class="text-input" value="<?php echo Handler::var('safe_post')["current-password"] ?? ''; ?>" placeholder="<?php _se('Enter your current password'); ?>" required>
                <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["current-password"] ?? ''; ?></span>
                <div class="input-below text-align-right"><a href="<?php echo get_base_url("account/password-forgot"); ?>"><i class="fas fa-key margin-right-5"></i><?php _se('Forgot password?'); ?></a></div>
            </div>
        <?php
                } ?>
        <div class="input-label input-password">
            <label for="new-password"><?php _se('New password'); ?></label>
            <input autocomplete="new-password" type="password" name="new-password" id="new-password" class="text-input" value="<?php echo Handler::var('safe_post')["new-password"] ?? ''; ?>" pattern="<?php echo getSetting('user_password_pattern'); ?>" rel="tooltip" title="<?php _se('%d characters min', getSetting('user_password_min_length')); ?>" data-tipTip="right" placeholder="<?php _se('Enter your new password'); ?>" required>
            <div class="input-password-strength"><span style="width: 0%" data-content="password-meter-bar"></span></div>
            <span class="input-warning red-warning" data-text="password-meter-message"><?php echo Handler::var('input_errors')["new-password"] ?? ''; ?></span>
        </div>
        <div class="input-label input-password">
            <label for="new-password-confirm"><?php _se('Confirm new password'); ?></label>
            <input autocomplete="new-password" type="password" name="new-password-confirm" id="new-password-confirm" class="text-input" value="<?php echo Handler::var('safe_post')["new-password-confirm"] ?? ''; ?>" placeholder="<?php _se('Re-enter your new password'); ?>" required>
            <span class="text-align-right input-warning input-below red-warning<?php echo isset(Handler::var('input_errors')["new-password-confirm"]) ? "" : " hidden-visibility"; ?>" data-text="<?php _se("Passwords don't match"); ?>"><?php if (isset(Handler::var('input_errors')["new-password-confirm"])) {
                    echo _s("Passwords don't match");
                } ?></span>
        </div>
    </div>
<?php } else { ?>
    <p class="margin-bottom-20"><?php if (!Handler::cond('dashboard_user')) {
                    _se('Add a password to be able to login using your username or email.');
                } else {
                    _se("This user doesn't have a password. Add one using this form.");
                } ?></p>
    <div class="c12 phablet-c1">
        <div class="input-label input-password">
            <label for="new-password"><?php _se('Password'); ?></label>
            <input type="password" name="new-password" id="new-password" class="text-input" value="<?php echo Handler::var('safe_post')["new-password"] ?? ''; ?>" pattern="<?php echo getSetting('user_password_pattern'); ?>" rel="tooltip" title="<?php _se('%d characters min', getSetting('user_password_min_length')); ?>" data-tipTip="right" placeholder="<?php _se('Enter your password'); ?>" required>
            <div class="input-password-strength"><span style="width: 0%" data-content="password-meter-bar"></span></div>
            <span class="input-warning red-warning" data-text="password-meter-message"><?php echo Handler::var('input_errors')["new-password"] ?? ''; ?></span>
        </div>
        <div class="input-label input-password">
            <label for="new-password-confirm"><?php _se('Confirm password'); ?></label>
            <input type="password" name="new-password-confirm" id="new-password-confirm" class="text-input" value="<?php echo Handler::var('safe_post')["new-password-confirm"] ?? ''; ?>" maxlength="255" placeholder="<?php _se('Re-enter your password'); ?>" required>
            <span class="input-warning red-warning<?php echo isset(Handler::var('input_errors')["new-password-confirm"]) ? "" : " hidden-visibility"; ?>" data-text="<?php _se("Passwords don't match"); ?>"><?php _se("Passwords don't match"); ?></span>
        </div>
    </div>
<?php } ?>
