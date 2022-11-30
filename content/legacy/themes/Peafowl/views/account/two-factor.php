<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_file('head'); ?>
<body id="login" class="full--wh">
	<div data-modal="unable" class="hidden">
        <span class="modal-box-title"><i class="fas fa-question-circle"></i> <?php _se('Unable to authenticate?'); ?></span>
        <p><?php _se('If you lost your authentication device you must contact the system administrator.'); ?></p>
    </div>
	<?php include_theme_file('custom_hooks/body_open'); ?>
	<div class="display-flex height-min-full">
		<?php include_theme_file('snippets/quickty/background_cover'); ?>
		<div class="flex-center">
			<div class="content-box card-box col-8-max text-align-center">
			<div class="fancy-box">
				<h1 class="fancy-box-heading"><i class="fas fa-key"></i> <?php _se('Two-factor authentication'); ?></h1>
                <div class="content-section"><?php _se('Enter the security code from your authenticator app.'); ?></div>
                <form method="post" autocomplete="off" data-action="validate">
					<fieldset class="fancy-fieldset">
                        <div class="input-with-button">
							<input autofocus inputmode="numeric" pattern="\d*" autocomplete="one-time-code" type="number" name="user-two-factor" id="form-user-two-factor" class="input" value="" placeholder="<?php _se('Security code'); ?>" required>
                            <button type="submit" tabindex="3" class="cursor-pointer icon--input-submit fas fa-arrow-alt-circle-right"></button>
						</div>
						<div class="text-align-left red-warning margin-top-5"><?php echo Handler::var('input_errors')['user-two-factor'] ?? ''; ?></div>
                        <div class="input-label-below text-align-right margin-top-5">
							<a class="user-select-none" data-modal="simple" data-target="unable"><i class="fas fa-question-circle margin-right-5"></i><?php _se('Unable to authenticate?'); ?></a>
						</div>
					</fieldset>
					<?php include_theme_file('snippets/quickty/recaptcha_form'); ?>
				</form>
			</div>
		</div>
	</div>
	<?php include_theme_file('snippets/quickty/top_left'); ?>
</div>
<?php include_theme_footer(); ?>
