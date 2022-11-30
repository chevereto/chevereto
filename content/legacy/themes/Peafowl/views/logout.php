<?php

use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_header(); ?>
<div class="center-box c24 margin-top-20">
	<div class="content-width">
		<div class="header default-margin-bottom">
			<h1 class="header-title"><?php _se('Logged out'); ?></h1>
		</div>
		<div>
			<p><?php _se('You have been logged off %s. Hope to see you soon.', Handler::var('safe_html_website_name')); ?></p>
			<div class="btn-container"><a href="<?php echo get_base_url(); ?>" class="btn btn-input default"><?php _se('Go to homepage'); ?></a></div>
		</div>
	</div>
</div>
<?php include_theme_footer(); ?>
