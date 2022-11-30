<?php
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_global;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php echo include_theme_file('mails/header'); ?>

<?php _se('We received a request to register the %n account at %w.', ['%n' => get_global('theme_mail')['user']['username'], '%w' => getSetting('website_name')]); ?>
<br><br>
<?php _se('To complete the process you must <a href="%s">activate your account</a>.', get_global('theme_mail')['link']); ?>
<br><br>
<?php _se('Alternatively you can copy and paste the URL into your browser: <a href="%s">%s</a>', ['%s' => get_global('theme_mail')['link']]); ?>
<br><br>
<?php _se("If you didn't intend this just ignore this message."); ?>
<br>
<?php _se('This request was made from IP: %s', get_client_ip()); ?>

<?php echo include_theme_file('mails/footer'); ?>
