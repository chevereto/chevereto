<?php

use Chevereto\Legacy\G\Handler;

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_header(); ?>
<div class="center-box c24 margin-top-20">
	<div class="content-width">
		<div class="header default-margin-bottom">
			<h1 class="header-title"><?php echo Handler::var('error_title') ?? 'Error'; ?></h1>
		</div>
		<div class="form-content">
			<p><?php echo Handler::var('error_message'); ?></p>
			<div class="btn-container"><a href="<?php echo get_base_url(); ?>" class="btn btn-input default"><?php _se('Go to homepage'); ?></a></div>
		</div>
	</div>
</div>
<?php require_theme_footer(); ?>
