<?php
use function Chevereto\Legacy\G\get_global;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo include_theme_file('mails/header');
_se('Hi %n, welcome to %w', ['%n' => get_global('theme_mail')['user']['name'], '%w' => getSetting('website_name')]); ?>
<br><br>
<?php _se('Now that your account is ready you can enjoy uploading your images, creating albums and setting the privacy of your content as well as many more cool things that you will discover.'); ?>
<br><br>
<?php _se('By the way, here is you very own awesome profile page: <a href="%u">%n</a>. Go ahead and customize it, its yours!.', ['%u' => get_global('theme_mail')['user']['url'], '%n' => get_global('theme_mail')['user']['username']]); ?>
<br><br>
<?php _se('Thank you for joining'); ?>,
<br>
<?php echo getSetting('website_name'); ?>
<?php echo include_theme_file('mails/footer'); ?>
