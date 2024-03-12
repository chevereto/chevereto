<?php
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_checkbox_html;
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
    <label for="<?php echo $label; ?>"><?php _se('Name'); ?></label>
    <input type="text" name="<?php echo $label; ?>" id="<?php echo $label; ?>" class="text-input" value="<?php echo $album["name"] ?? null; ?>" placeholder="<?php _se('Unnamed %s', _n('album', 'albums', 1)); ?>" maxlength="<?php echo getSetting('album_name_max_length'); ?>" required autocomplete="off">
</div>
<?php if (Handler::cond('content_manager') && ($GLOBALS['theme_include_args']['album-root'] ?? false)) { ?>
<div id="cta-form">
    <div class="input-label">
        <label><?php _se('Call to action'); ?></label>
        <?php echo get_checkbox_html([
                        'name' => 'cta-enable',
                        'label' => _s('Enable call to action buttons'),
                        'checked' => Handler::var('album')['cta_enable'] ?? false,
                    ]); ?>
    </div>
    <div id="cta-combo" class="soft-hidden">
        <p class="font-size-small"><?php _se('Call to action buttons will be displayed on the %s page and in content belonging to.', _n('album', 'albums', 1)); ?> <?php _se('You can use %emoji% or %package% icons.', [
        '%emoji%' => '<a href="https://unicode.org/emoji/charts/full-emoji-list.html" target="_blank"><span class="btn-icon">🙂 </span>Emoji</a>',
        '%package%' => '<a href="https://fontawesome.com/search?o=r&m=free&s=solid" target="_blank"><i class="fa-solid fa-font-awesome btn-icon"></i>Font Awesome</a>',
    ]); ?></p>
        <div id="cta-rows" class="position-relative"></div>
        <template id="cta-row-template">
            <div class="input-label cta-row" data-pos="%pos%">
                <div class="overflow-hidden">
                    <i class="fa-solid fa-sort cursor-grab"></i>
                    <span class="input-label-label" title="Drag to sort"><?php _se('Call to action'); ?> #%pos%</span>
                    <button data-action="cta-add" class="btn btn-small default float-right margin-left-5"><span class="fa-solid fa-add"></span></button>
                    <button data-action="cta-remove" class="btn btn-small default float-right"><span class="fa-solid fa-trash-alt"></span></button>
                </div>
                <div class="overflow-auto">
                    <div class="grid-columns c4 phablet-c1 margin-right-10 phone-margin-bottom-10 phablet-margin-bottom-10 phone-margin-right-0 phablet-margin-right-0">
                        <label for="cta-label_%pos%"><?php _se('Label'); ?></label>
                        <input type="text" name="cta-label_%pos%" id="cta-label_%pos%" class="text-input" value="%label%" placeholder="<?php _se('Buy now'); ?>">
                    </div>
                    <div class="grid-columns c4 phablet-c1 margin-right-10 phone-margin-bottom-10 phablet-margin-bottom-10 phone-margin-right-0 phablet-margin-right-0">
                        <label for="cta-icon_%pos%"><?php _se('Icon'); ?> <i data-content="icon" class="%iconClass%"></i></label>
                        <input data-required type="text" name="cta-icon_%pos%" id="cta-icon_%pos%" title="Emoji / Font Awesome" class="text-input" value="%icon%" placeholder="emoji">
                    </div>
                    <div class="grid-columns c8 phablet-c1">
                        <label for="cta-href_%pos%">Href</label>
                        <input data-required pattern="(\w+):\/\/.+" type="text" name="cta-href_%pos%" id="cta-href_%pos%" class="text-input" value="%href%" placeholder="protocol://">
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
<?php
    } ?>
<div class="input-label">
	<label for="form-album-description"><?php _se('Description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
	<textarea id="form-album-description" name="form-album-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this %s', _n('album', 'albums', 1)); ?>"><?php echo $album['description'] ?? null; ?></textarea>
</div>
<?php if (getSetting('website_privacy_mode') == 'public' || (getSetting('website_privacy_mode') == 'private' && getSetting('website_content_privacy_mode') == 'default')) {
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
        foreach ($permissions as $k => $v) {
            echo '<option ' . ((Login::isLoggedUser() == false && $k == 'private') ? ' disabled' : '') . ' value="' . $k . '"' . (($album['privacy'] ?? null) == $k ? '  selected' : '') . '>' . $v['label'] . '</option>';
        } ?>
		</select>
	</div>
</div>
<div id="form-privacy-combo">
	<div data-combo-value="password" class="switch-combo<?php echo($album['privacy'] ?? null) !== 'password' ? ' soft-hidden' : null; ?>">
		<div class="input-label overflow-auto">
			<div class="c8 grid-columns">
				<label for="form-album-password"><?php _se('Password'); ?></label>
				<input type="password" name="form-album-password" class="text-input" value="<?php echo $album['password'] ?? null; ?>" placeholder="<?php isset($album['password']) ? _se('Change password') : _se('Set password'); ?>" data-required<?php echo($album['privacy'] ?? null) == 'password' ? ' required' : null; ?>>
			</div>
		</div>
	</div>
</div>
<?php
    } ?>
