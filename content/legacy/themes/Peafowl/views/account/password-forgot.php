<?php

use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\obfuscate;

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
				<h1 class="fancy-box-heading"><i class="fas fa-key"></i> <?php _se('Forgot password?'); ?></h1>
				<?php
                    if (Handler::cond('process_done')) {
                        ?>
				<div class="content-section"><?php _se("An email with instructions to reset your password has been sent to the registered email address. If you don't receive the instructions try checking your junk or spam filters."); ?></div>
				<?php
                    } elseif (Login::isLoggedUser()) {
                        ?>
				<?php if (Handler::cond('error')) {
                            ?>
				<div class="content-section"><?php echo Handler::var('error'); ?></div>
				<?php
                        } else {
                            ?>
				<div class="content-section"><?php _se('A previous email has been sent with instructions to reset your password. If you did not receive the instructions try checking your junk or spam filters.'); ?></div>
				<div class="content-section"><a href="<?php echo get_base_url('account/password-forgot'); ?>" class="btn btn-input accent"><?php _se('Resend instructions'); ?></a></div>
				<?php
                        }
                    } else {
                        ?>
				<div class="content-section"><?php _se('Enter your username or email address to continue. You may need to check your spam folder or whitelist %s', obfuscate(Settings::get('email_from_email'))); ?></div>
				<form method="post" autocomplete="off" data-action="validate">
					<fieldset class="fancy-fieldset">
						<div>
							<input autofocus autocomplete="username" type="text" name="user-subject" id="form-user-subject" class="input" value="<?php echo Handler::var('safe_post')['user-subject'] ?? ''; ?>" placeholder="<?php _se('Username or Email address'); ?>" required>
							<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['user-subject'] ?? ''; ?></div>
						</div>
					</fieldset>
					<?php include_theme_file('snippets/quickty/recaptcha_form'); ?>
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
	<?php include_theme_file('snippets/quickty/top_left'); ?>
</div>
<?php if (Handler::var('post') && Handler::cond('error')) {
                    ?>
<script>
$(document).ready(function() {
	PF.fn.growl.call("<?php echo Handler::var('error'); ?>");
});
</script>
<?php
                }
include_theme_footer(); ?>
