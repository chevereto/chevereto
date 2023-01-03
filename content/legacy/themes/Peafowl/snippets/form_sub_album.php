<?php
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<input type="hidden" name="form-album-parent-id" value="<?php echo Handler::var('album')['id_encoded']; ?>">
<div class="input-label">
	<?php
        $label = 'form-album-name';
    ?>
    <label for="<?php echo $label; ?>"><?php _se('Name'); ?></label>
    <input type="text" name="<?php echo $label; ?>" class="text-input" value="" placeholder="<?php _se('Unnamed %s', _s('album')); ?>" maxlength="<?php echo getSetting('album_name_max_length'); ?>" required autocomplete="off">
</div>
<div class="input-label">
	<label for="form-album-description"><?php _se('Description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
	<textarea id="form-album-description" name="form-album-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this %s', _s('album')); ?>"></textarea>
</div>
<?php if (getSetting('website_privacy_mode') == 'public' or (getSetting('website_privacy_mode') == 'private' and getSetting('website_content_privacy_mode') == 'default')) {
        ?>
<div class="input-label overflow-auto">
    <div class="c8 grid-columns">
		<label for="form-privacy"><?php _se('Privacy'); ?></label>
		<select name="form-privacy" id="form-privacy" class="text-input" data-combo="form-privacy-combo" rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Who can view this content'); ?>">
			<?php
                $permissions = [
                    'public' => ['label' => _s('Public')],
                    'private' => ['label' => _s('Private (just me)')],
                    'private_but_link' => ['label' => _s('Private (anyone with the link)')],
                    'password' => ['label' => _s('Private (password protected)')],
                ];
        if (!Login::isLoggedUser()) {
            unset($permissions['private']);
        }
        foreach ($permissions as $k => $v) {
            echo '<option value="' . $k . '">' . $v['label'] . '</option>';
        } ?>
		</select>
	</div>
</div>
<div id="form-privacy-combo">
	<div data-combo-value="password" class="switch-combo soft-hidden">
		<div class="input-label overflow-auto">
			<div class="c8 grid-columns">
				<label for="form-album-password"><?php _se('Password'); ?></label>
				<input type="text" name="form-album-password" class="text-input" value="" placeholder="<?php _se('Set password'); ?>">
			</div>
		</div>
	</div>
</div>
<?php
    } ?>
