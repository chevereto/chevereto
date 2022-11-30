<?php

use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_file('head'); ?>
<body id="login" class="full--wh">
	<?php include_theme_file('custom_hooks/body_open'); ?>
	<div class="display-flex height-min-full">
		<?php include_theme_file('snippets/quickty/background_cover'); ?>
		<div class="flex-center">
			<div class="content-box card-box col-8-max text-align-center">
			<div class="fancy-box">
				<h1 class="fancy-box-heading"><i class="far fa-check-circle"></i> <?php _se('Your account is almost ready'); ?></h1>
				<div class="content-section"><?php _se("An email to %s has been sent with instructions to activate your account. The activation link is only valid for 48 hours. If you don't receive the instructions try checking your junk or spam filters.", '<b>' . Handler::var('signup_email') . '</b>'); ?></div>
				<div class="content-section"><a href="<?php echo get_base_url('account/resend-activation'); ?>" class="btn btn-input accent"><i class="btn-icon fas fa-check-circle"></i><span class="btn-text"><?php _se('Resend activation'); ?></span></a></div>
			</div>
		</div>
	</div>
	<?php include_theme_file('snippets/quickty/top_left'); ?>
</div>
<?php include_theme_footer(); ?>
