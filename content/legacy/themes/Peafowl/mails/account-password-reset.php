<?php
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_global;
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo include_theme_file('mails/header');

_se('We received a request to reset the password for your <a href="%u">%n</a> account.', ['%u' => get_global('theme_mail')['user']['url'], '%n' => get_global('theme_mail')['user']['name']]); ?>
<br><br>
<?php _se('To reset your password <a href="%s">follow this link</a>.', get_global('theme_mail')['link']); ?>
<br><br>
<?php _se('Alternatively you can copy and paste the URL into your browser: <a href="%s">%s</a>', ['%s' => get_global('theme_mail')['link']]); ?>
<br><br>
<?php _se("If you didn't intend this just ignore this message."); ?>
<br>
<?php _se('This request was made from IP: %s', get_client_ip()); ?>

<?php echo include_theme_file('mails/footer'); ?>
