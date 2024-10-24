<?php

use function Chevereto\Legacy\G\get_base_url;

use Chevereto\Legacy\Classes\Settings;
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
				<h1 class="fancy-box-heading"><i class="fas fa-key"></i> <?php _se('Reset password'); ?></h1>
				<?php
                    if (Handler::cond('process_done')) {
                        ?>
				<div class="content-section"><?php _se('Your password has been changed. You can now try logging in using your new password.'); ?></div>
				<div class="content-section"><a href="<?php echo get_base_url('login'); ?>" class="btn btn-input accent"><?php _se('Login now'); ?></a></div>
				<?php
                    } else {
                        ?>
				<div data-message="new-password-confirm" class="red-warning<?php echo (Handler::var('input_errors')['new-password-confirm'] ?? '') ? '' : ' hidden-visibility'; ?>" data-text="<?php _se("Passwords don't match"); ?>"><?php _se("Passwords don't match"); ?></div>
				<form class="content-section" method="post" autocomplete="off" data-action="validate">
					<fieldset class="fancy-fieldset">
						<div class="input-password position-relative">
							<input name="new-password" tabindex="1" type="password" placeholder="<?php _se('Enter your new password'); ?>" class="input" pattern="<?php echo Settings::USER_PASSWORD_PATTERN; ?>" rel="tooltip" title="<?php _se('%d characters min', Settings::USER_PASSWORD_MIN_LENGTH); ?>" data-tipTip="right" required>
							<div class="input-password-strength" rel="tooltip" title="<?php _se('Password strength'); ?>"><span style="width: 0%" data-content="password-meter-bar"></span></div>
						</div>
						<div class="input-password">
							<input name="new-password-confirm" tabindex="2" type="password" placeholder="<?php _se('Re-enter your new password'); ?>" class="input" required>
						</div>
					</fieldset>
					<?php require_theme_file('snippets/quickty/recaptcha_form'); ?>
					<div class="content-section">
						<button class="btn btn-input accent" type="submit"><i class="btn-icon fas fa-check-circle"></i><span class="btn-text"><?php _se('Submit'); ?></span></button>
					</div>
				</form>
				<?php
                    }
                ?>
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
