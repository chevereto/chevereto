<?php

use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('email', _s('Email')); ?>
<div class="margin-top-20">💡 <?php _se(
    "Don't forget to test %t at %s",
    [
        '%t' => _s('email delivery'),
        '%s' => '<a href="' . get_base_url('dashboard/settings/tools') . '" class="btn btn-small default"><i class="fas fa-tools"></i> ' . _s('Tools') . '</a>']
); ?></div>
<div class="input-label">
    <label for="email_from_name"><?php _se('From name'); ?></label>
    <div class="c9 phablet-c1"><input type="text" name="email_from_name" id="email_from_name" class="text-input" value="<?php echo Settings::get('email_from_name'); ?>" required></div>
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_from_name'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Sender name for emails sent to users.'); ?></div>
</div>
<div class="input-label">
    <label for="email_from_email"><?php _se('From email address'); ?></label>
    <div class="c9 phablet-c1"><input type="email" name="email_from_email" id="email_from_email" class="text-input" value="<?php echo Settings::get('email_from_email'); ?>" required></div>
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_from_email'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Sender email for emails sent to users.'); ?></div>
</div>
<div class="input-label">
    <label for="email_incoming_email"><?php _se('Incoming email address'); ?></label>
    <div class="c9 phablet-c1"><input type="email" name="email_incoming_email" id="email_incoming_email" class="text-input" value="<?php echo Settings::get('email_incoming_email'); ?>" required></div>
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_incoming_email'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Recipient for contact form and system alerts.'); ?></div>
</div>
<div class="input-label">
    <label for="email_mode"><?php _se('Email mode'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="email_mode" id="email_mode" class="text-input" data-combo="mail-combo">
        <?php
                echo get_select_options_html(['smtp' => 'SMTP', 'mail' => 'PHP mail() func.'], Handler::var('safe_post') ? Handler::var('safe_post')['email_mode'] : Settings::get('email_mode')); ?>
    </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_mode'] ?? ''; ?></div>
    <div class="input-below"><?php _se('How to send emails? SMTP recommended.'); ?></div>
</div>
<div id="mail-combo">
    <?php
                if (isset($GLOBALS['SMTPDebug'])) {
                    echo '<p class="highlight">' . nl2br($GLOBALS['SMTPDebug'] ?? '') . '</p>';
                } ?>
    <div data-combo-value="smtp" class="switch-combo c9 phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['email_mode'] : Settings::get('email_mode')) !== 'smtp') {
                    echo ' soft-hidden';
                } ?>">
        <div class="input-label">
            <label for="email_smtp_server"><?php _se('SMTP server and port'); ?></label>
            <div class="overflow-auto">
                <div class="c7 float-left">
                    <input type="text" name="email_smtp_server" id="email_smtp_server" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server'] ?? Settings::get('email_smtp_server'); ?>" placeholder="<?php _se('SMTP server'); ?>">
                </div>
                <div class="c2 float-left margin-left-10">
                    <select type="text" name="email_smtp_server_port" id="email_smtp_server_port" class="text-input">
                        <?php
                        echo get_select_options_html([25 => 25, 80 => 80, 465 => 465, 587 => 587], Handler::var('safe_post') ? Handler::var('safe_post')['email_smtp_server_port'] : Settings::get('email_smtp_server_port')); ?>
                    </select>
                </div>
            </div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_smtp_server'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_smtp_server_username"><?php _se('SMTP username'); ?></label>
            <input type="text" name="email_smtp_server_username" id="email_smtp_server_username" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server_username'] ?? Settings::get('email_smtp_server_username'); ?>">
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['email_smtp_server_username'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_smtp_server_password"><?php _se('SMTP password'); ?></label>
            <input type="password" name="email_smtp_server_password" id="email_smtp_server_password" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server_password'] ?? Settings::get('email_smtp_server_password'); ?>">
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['email_smtp_server_password'] ?? ''; ?></div>
        </div>
        <div class="input-label c5">
            <label for="email_smtp_server_security"><?php _se('SMTP security'); ?></label>
            <select type="text" name="email_smtp_server_security" id="email_smtp_server_security" class="text-input">
                <?php
                echo get_select_options_html(['tls' => 'TLS', 'ssl' => 'SSL', 'unsecured' => _s('Unsecured')], Handler::var('safe_post') ? Handler::var('safe_post')['email_smtp_server_security'] : Settings::get('email_smtp_server_security')); ?>
            </select>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_smtp_server_security'] ?? ''; ?></div>
        </div>
    </div>
</div>
