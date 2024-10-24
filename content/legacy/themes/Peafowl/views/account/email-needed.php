<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\getSetting;

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
				<h1 class="fancy-box-heading"><i class="fas fa-at"></i> <?php _se('Add your email address'); ?></h1>
				<div class="content-section"><?php _se(getSetting('require_user_email_confirmation') ? 'A confirmation link will be sent to this email with details to activate your account.' : 'You must add an email to continue with the account sign up.'); ?></div>
				<form class="content-section" method="post" autocomplete="off" data-action="validate">
					<fieldset class="fancy-fieldset">
						<div>
							<input type="email" name="email" class="input" autocomplete="off" value="<?php echo Handler::var('safe_post')['email'] ?? ''; ?>" placeholder="<?php _se('Your email address'); ?>" required>
							<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['email'] ?? ''; ?></div>
						</div>
					</fieldset>
					<?php require_theme_file('snippets/quickty/recaptcha_form'); ?>
					<div class="content-section">
						<button class="btn btn-input accent" type="submit"><i class="btn-icon fas fa-check-circle"></i><span class="btn-text"><?php _se('Submit'); ?></span></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php require_theme_file('snippets/quickty/top_left'); ?>
</div>
<?php if (Handler::var('post') && Handler::cond('error')) {
    ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
	PF.fn.growl.call("<?php echo Handler::var('error'); ?>");
});
</script>
<?php
}
require_theme_footer(); ?>
