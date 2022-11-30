<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;
use function Chevereto\Legacy\get_system_image_url;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$logo_header = getSetting('logo_' . getSetting('logo_type'));
?>
<?php include_theme_header(); ?>
<div id="maintenance-cover" style="background-image: url(<?php echo get_system_image_url(getSetting('maintenance_image')); ?>);">
	<div id="maintenance-cover-inner">
		<div id="maintenance-cover-content" class="c16 center-box">
            <a class="logo" href="<?php echo Handler::var('header_logo_link'); ?>"><?php if (getSetting('logo_type') !== 'text') { ?><img src="<?php echo get_system_image_url($logo_header); ?>" alt="<?php echo Handler::var('safe_html_website_name'); ?>"><?php } else { ?><?php echo Handler::var('safe_html_website_name'); ?><?php } ?></a>
			<h1><?php _se('Website under maintenance'); ?></h1>
			<p><?php _se("We're performing scheduled maintenance tasks in the website. Please come back in a few minutes."); ?></p>
		</div>
	</div>
</div>
<?php include_theme_footer(); ?>
