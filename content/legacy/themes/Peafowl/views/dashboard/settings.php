<?php

use function Chevereto\Legacy\G\get_input_auth_token;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
function email_mode_warning()
{
    if (empty(getSetting('email_mode'))) {
        echo '<div class="input-below"><span class="icon fas fa-info-circle color-fail"></span> ' . _s('This setting remains disabled until the email provider is configured.') . '</div>';
    }
}
function personal_mode_warning()
{
    if (getSetting('website_mode') == 'personal') {
        echo '<div class="input-below"><span class="icon fas fa-info-circle color-fail"></span> ' . _s('This setting remains disabled when using single profile website mode.') . '</div>';
    }
}
?>
				<form id="dashboard-settings" method="post" data-type="<?php echo Handler::var('dashboard'); ?>" data-action="validate" enctype="multipart/form-data">
					<?php echo get_input_auth_token(); ?>
					<?php
                        require sprintf('settings/%s.php', Handler::var('settings')['key']);
                        if (Handler::cond('show_submit')) {
                            ?>
	<div class="btn-container btn-container--fixed">
		<div class="content-width text-align-center">
			<button class="btn btn-input accent" type="submit" title="Ctrl/Cmd + Enter"><span class="fa fa-check-circle btn-icon"></span><span class="btn-text"><?php _se('Save changes'); ?></span></button>
		</div>
	</div>
<?php
                        } ?>
</form>
