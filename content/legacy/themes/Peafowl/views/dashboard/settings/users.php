<?php

use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\get_ini_bytes;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('users', _n('User', 'Users', 20)); ?>
<div class="input-label">
    <label for="user_profile_view"><?php _se('Profile view'); ?></label>
    <div class="c5 phablet-c1">
        <select type="text" name="user_profile_view" id="user_profile_view" class="text-input">
            <?php
            echo get_select_options_html(['files' => _n('File', 'Files', 20), 'albums' => _n('Album', 'Albums', 20)], Settings::get('user_profile_view')); ?>
        </select>
    </div>
    <div class="input-below"><?php _se('Determine the default view for user profile.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="enable_signups"><?php _se('Enable signups'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_signups" id="enable_signups" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_signups')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to allow users to signup.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <label for="enable_user_content_delete"><?php _se('Enable user content delete'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="enable_user_content_delete" id="enable_user_content_delete" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_user_content_delete')); ?>
        </select></div>
    <div class="input-below"><?php _se("Enable this if you want to allow users to delete their own content."); ?> <?php _se("This setting doesn't affect administrators."); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <label for="user_minimum_age"><?php _se('Minimum age required'); ?></label>
    <div class="c3"><input type="number" min="0" pattern="\d+" name="user_minimum_age" id="user_minimum_age" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?> value="<?php echo Handler::var('safe_post')['user_minimum_age'] ?? Settings::get('user_minimum_age'); ?>" placeholder="<?php _se('Empty'); ?>"></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['user_minimum_age'] ?? ''; ?></div>
    <div class="input-below"><?php _se("Leave it empty to don't require a minimum age to use the website."); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="notify_user_signups"><?php _se('Notify on user signup'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="notify_user_signups" id="notify_user_signups" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('notify_user_signups')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to get an email notification for each new user signup.'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="require_user_email_confirmation"><?php _se('Require email confirmation'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="require_user_email_confirmation" id="require_user_email_confirmation" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('require_user_email_confirmation')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if users must validate their email address on sign up.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <label for="require_user_email_social_signup"><?php _se('Require email for social signup'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="require_user_email_social_signup" id="require_user_email_social_signup" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('require_user_email_social_signup')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if users using social networks to register must provide an email address.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="user_image_avatar_max_filesize_mb"><?php _se('User avatar max. filesize'); ?> (MB)</label>
    <div class="c3"><input type="number" min="0" pattern="\d+" name="user_image_avatar_max_filesize_mb" id="user_image_avatar_max_filesize_mb" class="text-input" value="<?php echo Handler::var('safe_post')['user_image_avatar_max_filesize_mb'] ?? Settings::get('user_image_avatar_max_filesize_mb'); ?>" placeholder="MB" required></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['user_image_avatar_max_filesize_mb'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Max. allowed filesize for user avatar image. (Max allowed by server is %s)', format_bytes(get_ini_bytes(ini_get('upload_max_filesize')))); ?></div>
</div>
<div class="input-label">
    <label for="user_image_background_max_filesize_mb"><?php _se('User background max. filesize'); ?> (MB)</label>
    <div class="c3"><input type="number" min="0" pattern="\d+" name="user_image_background_max_filesize_mb" id="user_image_background_max_filesize_mb" class="text-input" value="<?php echo Handler::var('safe_post')['user_image_background_max_filesize_mb'] ?? Settings::get('user_image_background_max_filesize_mb'); ?>" placeholder="MB" required></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['user_image_background_max_filesize_mb'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Max. allowed filesize for user background image. (Max allowed by server is %s)', format_bytes(get_ini_bytes(ini_get('upload_max_filesize')))); ?></div>
</div>
