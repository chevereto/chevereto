<?php

use function Chevereto\Legacy\G\get_base_url;
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
					<h1 class="fancy-box-heading"><i class="fas fa-sign-in-alt"></i> <?php _se('Sign in with your account'); ?></h1>
					<?php if (getSetting('enable_signups')) {
    ?>
					<div class="content-section"><?php _se("Don't have an account? %signup% now.", ['%signup%' => '<a href=' . get_base_url('signup') . '><i class="fas fa-user-plus margin-right-5"></i>' . _s('Sign up') . '</a>']); ?></div>
					<?php
} ?>
					<form class="content-section" method="post" autocomplete="off" data-action="validate">
						<fieldset class="fancy-fieldset">
							<div>
								<input autofocus autocomplete="nickname" name="login-subject" tabindex="1" autocorrect="off" autocapitalize="off" type="text" placeholder="<?php _se('Username or Email address'); ?>" class="input" required>
							</div>
							<div class="input-with-button">
								<input autocomplete="current-password" name="password" tabindex="2" type="password" placeholder="<?php _se('Password'); ?>" class="input" required>
								<button type="submit" tabindex="3" class="cursor-pointer icon--input-submit fas fa-arrow-alt-circle-right"></button>
							</div>
						</fieldset>
						<div class="input-label-below text-align-right margin-top-5">
							<a href="<?php echo get_base_url('account/password-forgot'); ?>"><i class="fas fa-key margin-right-5"></i><?php _se('Forgot password?'); ?></a>
						</div>
						<?php require_theme_file('snippets/quickty/recaptcha_form'); ?>
					</form>
					<?php require_theme_file('snippets/quickty/login_providers'); ?>
				</div>
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
