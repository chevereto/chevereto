<?php
use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_file('head'); ?>
<body id="login" class="full--wh">
<?php
try {
	require_theme_file('custom_hooks/body_open');
} catch (Throwable $e) {
}
?>
	<div class="display-flex height-min-full">
		<?php require_theme_file('snippets/quickty/background_cover'); ?>
		<div class="flex-center">
			<div class="content-box card-box col-8-max text-align-center">
			<div class="fancy-box">
				<h1 class="fancy-box-heading"><i class="fas fa-at"></i> <?php _se('Email changed'); ?></h1>
				<div class="content-section"><?php _se('You have successfully changed your account email to %s', '<b>' . Login::getUser()['email'] . '</b>'); ?></div>
				<div class="content-section"><a href="<?php echo Login::getUser()['url']; ?>" class="btn btn-input accent"><i class="btn-icon fas fa-check-circle"></i><span class="btn-text"><?php _se('Go to my profile'); ?></span></a></div>
			</div>
		</div>
	</div>
	<?php require_theme_file('snippets/quickty/top_left'); ?>
</div>
<?php require_theme_footer(); ?>
