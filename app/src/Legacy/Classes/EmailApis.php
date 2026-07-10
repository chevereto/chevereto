<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use function Chevereto\Vars\env;

class EmailApis
{
    private static array $required = [
        'smtp' => ['email_smtp_server', 'email_smtp_server_port', 'email_smtp_server_security'],
        'ahasend' => ['email_ahasend_api_key'],
        'ses' => ['email_ses_access_key', 'email_ses_secret_key'],
        'azure' => ['email_azure_resource_name', 'email_azure_key'],
        'brevo' => ['email_brevo_api_key'],
        'infobip' => ['email_infobip_api_key', 'email_infobip_base_url'],
        'mailersend' => ['email_mailersend_api_key'],
        'mailgun' => ['email_mailgun_api_key', 'email_mailgun_domain'],
        'mailjet' => ['email_mailjet_access_key', 'email_mailjet_secret_key'],
        'mailomat' => ['email_mailomat_api_key'],
        'mailpace' => ['email_mailpace_api_token'],
        'mailtrap' => ['email_mailtrap_api_token'],
        'mandrill' => ['email_mandrill_api_key'],
        'microsoftgraph' => ['email_microsoftgraph_client_id', 'email_microsoftgraph_client_secret', 'email_microsoftgraph_tenant_id'],
        'postal' => ['email_postal_api_key', 'email_postal_base_url'],
        'postmark' => ['email_postmark_api_token'],
        'resend' => ['email_resend_api_key'],
        'scaleway' => ['email_scaleway_project_id', 'email_scaleway_api_key'],
        'sendgrid' => ['email_sendgrid_api_key'],
        'sweego' => ['email_sweego_api_key'],
    ];

    private static array $apis = [
        'smtp' => 'SMTP',
        'mail' => 'PHP mail()',
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

    public static function getEnabled(): array
    {
        $enabledApis = self::$apis;
        if (env()['CHEVERETO_SERVICING'] !== 'server') {
            unset($enabledApis['mail']);
        }
        if (env()['CHEVERETO_CONTEXT'] === 'saas') {
            unset($enabledApis['smtp'], $enabledApis['mail']);
        }

        return $enabledApis;
    }

    public static function getRequiredFields(string $api): array
    {
        return self::$required[$api] ?? [];
    }
}
