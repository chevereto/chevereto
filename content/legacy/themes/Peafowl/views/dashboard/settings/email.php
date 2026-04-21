<?php

use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Vars\env;

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
<?php
$mailOptions = [
    '' => _s('Select email API'),
    'smtp' => _s('SMTP'),
    'mail' => _s('PHP mail() func.'),
    'ahasend' => 'AhaSend',
    'ses' => 'Amazon SES',
    'azure' => 'Azure',
    'brevo' => 'Brevo',
    'infobip' => 'Infobip',
    'mailersend' => 'MailerSend',
    'mailgun' => 'Mailgun',
    'mailjet' => 'Mailjet',
    'mailomat' => 'Mailomat',
    'mailpace' => 'MailPace',
    'mailtrap' => 'Mailtrap',
    'mandrill' => 'Mandrill',
    'microsoftgraph' => 'Microsoft Graph',
    'postal' => 'Postal',
    'postmark' => 'Postmark',
    'resend' => 'Resend',
    'scaleway' => 'Scaleway',
    'sendgrid' => 'SendGrid',
    'sweego' => 'Sweego',
];
if (env()['CHEVERETO_SERVICING'] !== 'server') {
    unset($mailOptions['mail']);
}
if (env()['CHEVERETO_CONTEXT'] === 'saas') {
    unset($mailOptions['smtp'], $mailOptions['mail']);
}
$currentEmailMode = Handler::var('safe_post') ? Handler::var('safe_post')['email_mode'] : Settings::get('email_mode');
if(!array_key_exists($currentEmailMode, $mailOptions)) {
    $currentEmailMode = '';
}
?>
<div class="input-label">
    <label for="email_mode"><?php _se('Email %s', 'API'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="email_mode" id="email_mode" class="text-input" data-combo="mail-combo">
        <?php echo get_select_options_html($mailOptions, $currentEmailMode); ?>
    </select></div>
    <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_mode'] ?? ''; ?></div>
</div>
<div id="mail-combo">
    <?php if (isset($GLOBALS['SMTPDebug'])) {
        echo '<p class="highlight padding-5 c9 phablet-c1 margin-bottom-10">' . nl2br($GLOBALS['SMTPDebug']) . '</p>';
    } ?>
    <div data-combo-value="smtp" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'smtp') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_smtp_server"><?php _se('SMTP server and port'); ?></label>
            <div class="overflow-auto">
                <div class="c7 float-left">
                    <input type="text" name="email_smtp_server" id="email_smtp_server" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server'] ?? Settings::get('email_smtp_server'); ?>" placeholder="<?php _se('Server'); ?>">
                </div>
                <div class="c2 float-left margin-left-10">
                    <input type="number" min="1" max="65535" step="1" name="email_smtp_server_port" id="email_smtp_server_port" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server_port'] ?? Settings::get('email_smtp_server_port'); ?>" placeholder="<?php _se('Port'); ?>">
                </div>
            </div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_smtp_server'] ?? ''; ?></div>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_smtp_server_port'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_smtp_server_username"><?php _se('SMTP username'); ?></label>
            <input type="text" name="email_smtp_server_username" id="email_smtp_server_username" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server_username'] ?? Settings::get('email_smtp_server_username'); ?>" placeholder="<?php _se('Username'); ?>">
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['email_smtp_server_username'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_smtp_server_password"><?php _se('SMTP password'); ?></label>
            <input type="password" name="email_smtp_server_password" id="email_smtp_server_password" class="text-input" value="<?php echo Handler::var('safe_post')['email_smtp_server_password'] ?? Settings::get('email_smtp_server_password'); ?>" placeholder="<?php _se('Password'); ?>">
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['email_smtp_server_password'] ?? ''; ?></div>
        </div>
        <div class="input-label c5">
            <label for="email_smtp_server_security"><?php _se('SMTP security'); ?></label>
            <select type="text" name="email_smtp_server_security" id="email_smtp_server_security" class="text-input">
                <?php echo get_select_options_html(['tls' => 'TLS', 'ssl' => 'SSL', 'unsecured' => _s('Unsecured')], Handler::var('safe_post') ? Handler::var('safe_post')['email_smtp_server_security'] : Settings::get('email_smtp_server_security')); ?>
            </select>
            <div class="input-below input-warning red-warning clear-both"><?php echo Handler::var('input_errors')['email_smtp_server_security'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="ahasend" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'ahasend') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_ahasend_api_key"><?php _se('%s API key', 'AhaSend'); ?></label>
            <input type="text" name="email_ahasend_api_key" id="email_ahasend_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_ahasend_api_key'] ?? Settings::get('email_ahasend_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_ahasend_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="ses" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'ses') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_ses_access_key"><?php _se('%s access key', 'Amazon SES'); ?></label>
            <input type="text" name="email_ses_access_key" id="email_ses_access_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_ses_access_key'] ?? Settings::get('email_ses_access_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_ses_access_key'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_ses_secret_key"><?php _se('%s secret key', 'Amazon SES'); ?></label>
            <input type="password" name="email_ses_secret_key" id="email_ses_secret_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_ses_secret_key'] ?? Settings::get('email_ses_secret_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_ses_secret_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="azure" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'azure') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_azure_resource_name"><?php _se('%s resource name', 'Azure'); ?></label>
            <input type="text" name="email_azure_resource_name" id="email_azure_resource_name" class="text-input" value="<?php echo Handler::var('safe_post')['email_azure_resource_name'] ?? Settings::get('email_azure_resource_name'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_azure_resource_name'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_azure_key"><?php _se('%s access key', 'Azure'); ?></label>
            <input type="password" name="email_azure_key" id="email_azure_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_azure_key'] ?? Settings::get('email_azure_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_azure_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="brevo" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'brevo') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_brevo_api_key"><?php _se('%s API key', 'Brevo'); ?></label>
            <input type="text" name="email_brevo_api_key" id="email_brevo_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_brevo_api_key'] ?? Settings::get('email_brevo_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_brevo_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="infobip" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'infobip') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_infobip_api_key"><?php _se('%s API key', 'Infobip'); ?></label>
            <input type="text" name="email_infobip_api_key" id="email_infobip_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_infobip_api_key'] ?? Settings::get('email_infobip_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_infobip_api_key'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_infobip_base_url"><?php _se('%s base URL', 'Infobip'); ?></label>
            <input type="text" name="email_infobip_base_url" id="email_infobip_base_url" class="text-input" value="<?php echo Handler::var('safe_post')['email_infobip_base_url'] ?? Settings::get('email_infobip_base_url'); ?>" placeholder="e.g. xxxxx.api.infobip.com">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_infobip_base_url'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailersend" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailersend') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailersend_api_key"><?php _se('%s API key', 'MailerSend'); ?></label>
            <input type="text" name="email_mailersend_api_key" id="email_mailersend_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailersend_api_key'] ?? Settings::get('email_mailersend_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailersend_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailgun" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailgun') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailgun_api_key"><?php _se('%s API key', 'Mailgun'); ?></label>
            <input type="text" name="email_mailgun_api_key" id="email_mailgun_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailgun_api_key'] ?? Settings::get('email_mailgun_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailgun_api_key'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_mailgun_domain"><?php _se('%s domain', 'Mailgun'); ?></label>
            <input type="text" name="email_mailgun_domain" id="email_mailgun_domain" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailgun_domain'] ?? Settings::get('email_mailgun_domain'); ?>" placeholder="e.g. mg.example.com">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailgun_domain'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailjet" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailjet') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailjet_access_key"><?php _se('%s API key', 'Mailjet'); ?></label>
            <input type="text" name="email_mailjet_access_key" id="email_mailjet_access_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailjet_access_key'] ?? Settings::get('email_mailjet_access_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailjet_access_key'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_mailjet_secret_key"><?php _se('%s secret key', 'Mailjet'); ?></label>
            <input type="password" name="email_mailjet_secret_key" id="email_mailjet_secret_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailjet_secret_key'] ?? Settings::get('email_mailjet_secret_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailjet_secret_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailomat" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailomat') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailomat_api_key"><?php _se('%s API key', 'Mailomat'); ?></label>
            <input type="text" name="email_mailomat_api_key" id="email_mailomat_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailomat_api_key'] ?? Settings::get('email_mailomat_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailomat_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailpace" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailpace') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailpace_api_token"><?php _se('%s API token', 'MailPace'); ?></label>
            <input type="text" name="email_mailpace_api_token" id="email_mailpace_api_token" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailpace_api_token'] ?? Settings::get('email_mailpace_api_token'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailpace_api_token'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mailtrap" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mailtrap') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mailtrap_api_token"><?php _se('%s API token', 'Mailtrap'); ?></label>
            <input type="text" name="email_mailtrap_api_token" id="email_mailtrap_api_token" class="text-input" value="<?php echo Handler::var('safe_post')['email_mailtrap_api_token'] ?? Settings::get('email_mailtrap_api_token'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mailtrap_api_token'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="mandrill" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'mandrill') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_mandrill_api_key"><?php _se('%s API key', 'Mandrill'); ?></label>
            <input type="text" name="email_mandrill_api_key" id="email_mandrill_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_mandrill_api_key'] ?? Settings::get('email_mandrill_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_mandrill_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="microsoftgraph" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'microsoftgraph') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_microsoftgraph_client_id"><?php _se('%s client app ID', 'Microsoft Graph'); ?></label>
            <input type="text" name="email_microsoftgraph_client_id" id="email_microsoftgraph_client_id" class="text-input" value="<?php echo Handler::var('safe_post')['email_microsoftgraph_client_id'] ?? Settings::get('email_microsoftgraph_client_id'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_microsoftgraph_client_id'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_microsoftgraph_client_secret"><?php _se('%s client secret', 'Microsoft Graph'); ?></label>
            <input type="password" name="email_microsoftgraph_client_secret" id="email_microsoftgraph_client_secret" class="text-input" value="<?php echo Handler::var('safe_post')['email_microsoftgraph_client_secret'] ?? Settings::get('email_microsoftgraph_client_secret'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_microsoftgraph_client_secret'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_microsoftgraph_tenant_id"><?php _se('%s tenant ID', 'Microsoft Graph'); ?></label>
            <input type="text" name="email_microsoftgraph_tenant_id" id="email_microsoftgraph_tenant_id" class="text-input" value="<?php echo Handler::var('safe_post')['email_microsoftgraph_tenant_id'] ?? Settings::get('email_microsoftgraph_tenant_id'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_microsoftgraph_tenant_id'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="postal" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'postal') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_postal_api_key"><?php _se('%s API key', 'Postal'); ?></label>
            <input type="text" name="email_postal_api_key" id="email_postal_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_postal_api_key'] ?? Settings::get('email_postal_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_postal_api_key'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_postal_base_url"><?php _se('%s server URL', 'Postal'); ?></label>
            <input type="text" name="email_postal_base_url" id="email_postal_base_url" class="text-input" value="<?php echo Handler::var('safe_post')['email_postal_base_url'] ?? Settings::get('email_postal_base_url'); ?>" placeholder="e.g. postal.example.com">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_postal_base_url'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="postmark" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'postmark') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_postmark_api_token"><?php _se('%s server API token', 'Postmark'); ?></label>
            <input type="text" name="email_postmark_api_token" id="email_postmark_api_token" class="text-input" value="<?php echo Handler::var('safe_post')['email_postmark_api_token'] ?? Settings::get('email_postmark_api_token'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_postmark_api_token'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="resend" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'resend') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_resend_api_key"><?php _se('%s API key', 'Resend'); ?></label>
            <input type="text" name="email_resend_api_key" id="email_resend_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_resend_api_key'] ?? Settings::get('email_resend_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_resend_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="scaleway" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'scaleway') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_scaleway_project_id"><?php _se('%s project ID', 'Scaleway'); ?></label>
            <input type="text" name="email_scaleway_project_id" id="email_scaleway_project_id" class="text-input" value="<?php echo Handler::var('safe_post')['email_scaleway_project_id'] ?? Settings::get('email_scaleway_project_id'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_scaleway_project_id'] ?? ''; ?></div>
        </div>
        <div class="input-label">
            <label for="email_scaleway_api_key"><?php _se('%s API key', 'Scaleway'); ?></label>
            <input type="password" name="email_scaleway_api_key" id="email_scaleway_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_scaleway_api_key'] ?? Settings::get('email_scaleway_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_scaleway_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="sendgrid" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'sendgrid') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_sendgrid_api_key"><?php _se('%s API key', 'SendGrid'); ?></label>
            <input type="text" name="email_sendgrid_api_key" id="email_sendgrid_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_sendgrid_api_key'] ?? Settings::get('email_sendgrid_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_sendgrid_api_key'] ?? ''; ?></div>
        </div>
    </div>
    <div data-combo-value="sweego" class="switch-combo c9 phablet-c1<?php if ($currentEmailMode !== 'sweego') {
        echo ' soft-hidden';
    } ?>">
        <div class="input-label">
            <label for="email_sweego_api_key"><?php _se('%s API key', 'Sweego'); ?></label>
            <input type="text" name="email_sweego_api_key" id="email_sweego_api_key" class="text-input" value="<?php echo Handler::var('safe_post')['email_sweego_api_key'] ?? Settings::get('email_sweego_api_key'); ?>">
            <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['email_sweego_api_key'] ?? ''; ?></div>
        </div>
    </div>
</div>
