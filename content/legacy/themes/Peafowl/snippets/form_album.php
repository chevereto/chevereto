<?php
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php $album = Handler::var('album_safe_html'); ?>
<div class="input-label">
	<?php
        $label = 'form-album-name';
    ?>
    <label for="<?php echo $label; ?>"><?php _se('Album name'); ?></label>
    <input type="text" name="<?php echo $label; ?>" class="text-input" value="<?php echo $album["name"] ?? null; ?>" placeholder="<?php _se('Album name'); ?>" maxlength="<?php echo getSetting('album_name_max_length'); ?>" required autocomplete="off">
	<?php if (Login::isLoggedUser() && !isset($GLOBALS['theme_include_args'], $GLOBALS['theme_include_args']['album-switch'])) {
        ?>
    <span data-action="album-switch" class="btn-alt c7"><?php _se('or'); ?> <a data-switch="move-existing-album"><i class="fas fa-exchange-alt margin-right-5"></i><?php _se('move to existing album'); ?></a></span>
	<?php
    } ?>
</div>
<div class="input-label">
	<label for="form-album-description"><?php _se('Album description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
	<textarea id="form-album-description" name="form-album-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this album'); ?>"><?php echo $album['description'] ?? null; ?></textarea>
</div>
<?php if (getSetting('website_privacy_mode') == 'public' or (getSetting('website_privacy_mode') == 'private' and getSetting('website_content_privacy_mode') == 'default')) {
        ?>
<div class="input-label overflow-auto">
    <div class="c7 grid-columns">
		<label for="form-privacy"><?php _se('Album privacy'); ?></label>
		<select name="form-privacy" id="form-privacy" class="text-input" data-combo="form-privacy-combo" rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Who can view this content'); ?>">
			<?php
                $permissions = [
                    'public' => ['label' => _s('Public')],
                    'private' => ['label' => _s('Private (just me)')],
                    'private_but_link' => ['label' => _s('Private (anyone with the link)')],
                    'password' => ['label' => _s('Private (password protected)')],
                ];
        foreach ($permissions as $k => $v) {
            echo '<option ' . ((Login::isLoggedUser() == false && $k == 'private') ? ' disabled' : '') . ' value="' . $k . '"' . (($album['privacy'] ?? null) == $k ? '  selected' : '') . '>' . $v['label'] . '</option>';
        } ?>
		</select>
	</div>
</div>
<div id="form-privacy-combo">
	<div data-combo-value="password" class="switch-combo<?php echo($album['privacy'] ?? null) !== 'password' ? ' soft-hidden' : null; ?>">
		<div class="input-label overflow-auto">
			<div class="c7 grid-columns">
				<label for="form-album-password"><?php _se('Album password'); ?></label>
                <p></p>
				<input type="text" name="form-album-password" class="text-input" placeholder="<?php isset($album['password']) ? _se('Change password') : _se('Set password'); ?>" data-required<?php echo($album['privacy'] ?? null) == 'password' ? ' required' : null; ?>>
			</div>
		</div>
	</div>
</div>
<?php
    } ?>
