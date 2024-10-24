<?php

use function Chevereto\Legacy\G\get_input_auth_token;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_header(); ?>
<div class="content-width">
	<div class="content-password-gate">
		<div class="c16 center-box">
			<h1><span class="icon fas fa-lock"></span><?php _se('This content is password protected.'); ?></h1>
			<p></p>
			<p><?php _se('Please enter your password to continue.'); ?></p>
			<form method="post" autocomplete="off" data-action="validate">
				<?php echo get_input_auth_token(); ?>
				<div class="input-label c8 center-box">
					<label for="content-password"><?php _se('Password'); ?></label>
					<input type="password" id="content-password" name="content-password" class="text-input" required>
				</div>
				<?php if (Handler::cond('captcha_needed') && Handler::var('captcha_html') !== null) {
    ?>
				<div class="input-label center-box">
					<?php echo Handler::var('captcha_html'); ?>
				</div>
				<?php
} ?>
				<div class="btn-container margin-bottom-0">
                    <button class="btn btn-input accent" type="submit"><i class="fas fa-unlock"></i> <?php _se('Unlock'); ?></button>
                </div>
			</form>
		</div>
	</div>
</div>
<?php if (Handler::cond('error') && Handler::var('error') !== null) {
        ?>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	PF.fn.growl.call("<?php echo Handler::var('error'); ?>");
});
</script>
<?php
    } ?>
<?php require_theme_footer(); ?>
