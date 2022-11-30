<?php

use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\getSetting;

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
					<h1 class="fancy-box-heading"><i class="fas fa-user-plus"></i> <?php _se('Create account'); ?></h1>
					<div class="content-section"><?php _se('Already have an account? %s now.', '<a href="' . get_base_url('login') . '"><i class="fas fa-sign-in-alt margin-right-5"></i>' . _s('Login') . '</a>'); ?> <?php
                        if (Handler::cond('show_resend_activation')) {
                            ?><?php _se('If you have already signed up maybe you need to request to %s to activate your account.', '<a href="' . get_base_url('account/resend-activation') . '"><i class="fas fa-sync-alt margin-right-5"></i>' . _s('resend account activation') . '</a>'); ?><?php
                        } else {
                            ?><?php _se('You can also %s.', '<a href="' . get_base_url('account/resend-activation') . '"><i class="fas fa-sync-alt margin-right-5"></i>' . _s('resend account activation') . '</a>'); ?></div>
					<?php
                        }
                    ?>
					<form class="content-section" method="post" autocomplete="off" data-action="validate">
						<fieldset class="fancy-fieldset">
							<div class="position-relative">
								<input autofocus autocomplete="email" name="email" tabindex="1" autocomplete="off" autocorrect="off" autocapitalize="off" type="email" placeholder="<?php _se('Email address'); ?>" class="input" required value="<?php echo Handler::var('safe_post')['email'] ?? ''; ?>">
								<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['email'] ?? ''; ?></span>
							</div>
							<div class="position-relative">
								<input autocomplete="nickname" name="username" tabindex="2" autocomplete="off" autocorrect="off" autocapitalize="off" type="text" class="input" value="<?php echo Handler::var('safe_post')['username'] ?? ''; ?>" pattern="<?php echo getSetting('username_pattern'); ?>" rel="tooltip" title='<?php _se('%i to %f characters<br>Letters, numbers and "_"', ['%i' => getSetting('username_min_length'), '%f' => getSetting('username_max_length')]); ?>' data-tipTip="right" placeholder="<?php _se('Username'); ?>" required>
								<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['username'] ?? ''; ?></div>
							</div>
							<div class="input-password margin-bottom-10 position-relative">
								<input autocomplete="new-password" name="password" tabindex="4" type="password" placeholder="<?php _se('Password'); ?>" class="input" pattern="<?php echo getSetting('user_password_pattern'); ?>" rel="tooltip" title="<?php _se('%d characters min', getSetting('user_password_min_length')); ?>" data-tipTip="right" required>
								<div class="input-password-strength" rel="tooltip" title="<?php _se('Password strength'); ?>"><span style="width: 0%" data-content="password-meter-bar"></span></div>
							</div>
							<?php
                                if (getSetting('user_minimum_age') > 0) {
                                    ?>
							<div class="input-label text-align-left">
								<div class="checkbox-label"><label for="form-minimum-age-signup"><input type="checkbox" name="minimum-age-signup" id="form-minimum-age-signup" value="1" required><?php _se("I'm at least %s years old", getSetting('user_minimum_age')); ?></label></div>
								<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['minimum-age-signup'] ?? ''; ?></div>
							</div>
							<?php
                                } ?>
							<div class="input-label text-align-left">
								<div class="checkbox-label">
									<label for="signup-accept-terms-policies">
										<input type="checkbox" name="signup-accept-terms-policies" id="signup-accept-terms-policies" value="1" required>
										<span><?php echo Handler::var('tos_privacy_agreement'); ?></span>
									</label>
								</div>
								<div class="text-align-left red-warning"><?php echo Handler::var('input_errors')['signup-accept-terms-policies'] ?? ''; ?></div>
							</div>
						</fieldset>
						<?php include_theme_file('snippets/quickty/recaptcha_form'); ?>
						<div class="btn-container">
							<button class="btn btn-input accent" type="submit"><i class="btn-icon fas fa-user-plus"></i><span class="btn-text"><?php _se('Create account'); ?></span></button>
						</div>
					</form>
					<?php include_theme_file('snippets/quickty/login_providers'); ?>
				</div>
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
