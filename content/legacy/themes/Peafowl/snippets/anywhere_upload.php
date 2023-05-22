<?php

use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (Login::isLoggedUser()) {
    $user_albums = [];
    if (Login::getUser()['album_count'] > 0) {
        $user_albums = Handler::cond('owner')
            && Handler::var('user_items_editor') !== null
            && isset(Handler::var('user_items_editor')['user_albums'])
            ? Handler::var('user_items_editor')['user_albums']
            : User::getAlbums(Login::getUser());
    }
}
?>
<div id="anywhere-upload" class="no-select upload-box upload-box--fixed upload-box--hidden queueEmpty" data-queue-size="0">
	<div class="content-width">
    	<div class="upload-box-inner">
        	<div class="upload-box-heading c18 center-box">
				<div class="upload-box-status">
					<div data-group="upload">
						<span class="icon fas fa-photo-video color-accent cursor-pointer" data-trigger="anywhere-upload-input"></span>
						<div class="heading device-mobile--hide"><a data-trigger="anywhere-upload-input"><?php _se('Drag and drop or paste images here to upload'); ?></a></div>
						<div class="heading device-nonmobile--hide"><a data-trigger="anywhere-upload-input"><?php _se('Select the images to upload'); ?></a></div>
                        <?php
                            $iconBrowse = '<i class="fas fa-folder-plus margin-right-5"></i>';
                            $iconUrl = '<i class="fas fa-link margin-right-5"></i>';
                            $iconCamera = '<i class="fas fa-camera margin-right-5"></i>';
                            $you_can_add_two = getSetting('enable_uploads_url')
                                ? _s('You can also %i or %u.')
                                : _s('You can also %s.');
                            $you_can_add_two_tr = [
                                '%i' => '<a data-trigger="anywhere-upload-input">' . $iconBrowse . _s('browse from your computer') . '</a>',
                                '%u' => '<a data-modal="form" data-target="anywhere-upload-paste-url">' . $iconUrl . _s('add image URLs') . '</a>',
                            ];
                            $you_can_add_two_tr['%s'] = $you_can_add_two_tr['%i'];
                            $you_can_add_three = getSetting('enable_uploads_url')
                                ? _s('You can also %i, %c or %u.')
                                : _s('You can also %i or %c.');
                        ?>
						<div class="device-mobile--hide upload-box-status-text"><?php echo strtr($you_can_add_two, $you_can_add_two_tr); ?></div>
						<div class="device-nonmobile--hide upload-box-status-text"><?php echo strtr($you_can_add_three, [
                            '%i' => '<a data-trigger="anywhere-upload-input">' . $iconBrowse . _s('browse from your device') . '</a>',
                            '%c' => '<a data-trigger="anywhere-upload-input-camera">' . $iconCamera . _s('take a picture') . '</a>',
                            '%u' => '<a data-modal="form" data-target="anywhere-upload-paste-url">' . $iconUrl . _s('add image URLs') . '</a>',
                        ]); ?></div>
                        <div class="upload-box-allowed-files margin-top-10">
                            <span><?php echo str_replace(',', ' ', strtoupper(getSetting('upload_enabled_image_formats'))); ?></span>
                            <span class="margin-left-5"><?php echo getSetting('upload_max_filesize_mb') . ' MB'; ?>
                            <?php
                                if (getSetting('upload_max_filesize_mb_bak') != null && getSetting('upload_max_filesize_mb') != getSetting('upload_max_filesize_mb_bak')) {
                                    ?>
                            <span class="fas fa-exclamation-triangle" rel="tooltip" data-tiptip="top" title="<?php _se('Register to get %s', format_bytes(get_bytes(getSetting('upload_max_filesize_mb_bak') . 'MB'))); ?>"></span>
                            <?php
                                }
                            ?></span>
                        </div>
					</div>
					<div data-group="upload-queue-ready" class="soft-hidden">
						<span class="icon fas fa-photo-video color-accent cursor-pointer" data-trigger="anywhere-upload-input"></span>
						<div class="heading device-mobile--hide"><?php _se('Customize upload by %action% on any preview', ['%action%' => _s('clicking')]); ?></div>
						<div class="heading device-nonmobile--hide"><?php _se('Customize upload by %action% on any preview', ['%action%' => _s('touching')]); ?></div>
                        <div class="device-mobile--hide upload-box-status-text"><?php echo strtr($you_can_add_two, $you_can_add_two_tr); ?></div>
						<div class="device-nonmobile--hide upload-box-status-text"><?php echo strtr($you_can_add_three, [
                            '%i' => '<a data-trigger="anywhere-upload-input">' . $iconBrowse . _s('browse from your device') . '</a>',
                            '%c' => '<a data-trigger="anywhere-upload-input-camera">' . $iconCamera . _s('take a picture') . '</a>',
                            '%u' => '<a data-modal="form" data-target="anywhere-upload-paste-url">' . $iconUrl . _s('add image URLs') . '</a>',
                        ]); ?></div>
					</div>
					<div data-group="uploading" class="soft-hidden">
						<span class="icon fas fa-cloud-upload-alt color-accent"></span>
						<div class="heading"><?php _se('Uploading %q %o', [
                            '%q' => '<span data-text="queue-size">0</span>',
                            '%o' => '<span data-text="queue-objects">' . _n('file', 'files', 10) . '</span>',
                        ]); ?> (<span data-text="queue-progress">0</span>% <?php _se('complete'); ?>)</div>
						<div class="upload-box-status-text"><?php _se('The queue is being uploaded, it should take just a few seconds to complete.'); ?></div>
					</div>
					<div data-group="upload-result" data-result="success" class="soft-hidden">
						<span class="icon fas fa-check-circle color-success"></span>
						<div class="heading"><?php _se('Upload complete'); ?></div>
						<div class="upload-box-status-text">
							<div data-group="user" class="soft-hidden">
								<div data-group="user-stream" class="soft-hidden">
								<?php
                                    $iconUser = '<i class="fas fa-user margin-right-5"></i>';
                                    $iconAlbum = '<i class="fas fa-images margin-right-5"></i>';
                                    $iconMove = '<i class="fas fa-exchange-alt margin-right-5"></i>';
                                    $iconSignup = '<i class="fas fa-user-plus margin-right-5"></i>';
                                    $iconSignin = '<i class="fas fa-sign-in-alt margin-right-5"></i>';
                                    $uploaded_message = _s('Uploaded content added to %s.') . ' ';
                                    if (isset(Login::getUser()['album_count']) && Login::getUser()['album_count'] > 0) {
                                        $uploaded_message .= _s('You can %c with the content just uploaded or %m.');
                                    } else {
                                        $uploaded_message .= _s('You can %c with the content just uploaded.');
                                    }
                                    echo strtr($uploaded_message, [
                                        '%s' => '<a data-link="upload-target">' . $iconUser . '<span data-text="upload-target"></span></a>',
                                        '%c' => '<a data-modal="form" data-target="form-uploaded-create-album">' . $iconAlbum . _s('create new %s', _n('album', 'albums', 1)) . '</a>',
                                        '%m' => '<a data-modal="form" data-target="form-uploaded-move-album">' . $iconMove . _s('move it to an existing %s', _n('album', 'albums', 1)) . '</a>',
                                    ]);
                                ?>
								</div>
								<div data-group="user-album" class="soft-hidden"><?php _se('Uploaded content added to %s.', '<a data-link="upload-target">' . $iconUser . '<span data-text="upload-target"></span></a>'); ?></div>
							</div>
							<div data-group="guest" class="soft-hidden">
							<?php
                                $uploaded_message = _s('You can %c with the content just uploaded.') . ' ' . _s('You must %s or %l to save this content into your account.');
                                echo strtr($uploaded_message, [
                                    '%c' => '<a data-modal="form" data-target="form-uploaded-create-album">' . $iconAlbum . _s('create new %s', _n('album', 'albums', 1)) . '</a>',
                                    '%s' => '<a href="' . get_base_url("signup") . '">' . $iconSignup . _s('create an account') . '</a>',
                                    '%l' => '<a href="' . get_base_url("login") . '">' . $iconSignin . _s('sign in') . '</a>'
                                ]);
                            ?>
							</div>
						</div>
					</div>
					<div data-group="upload-result" data-result="error" class="soft-hidden">
						<span class="icon fas fa-times color-fail"></span>
						<div class="heading"><?php _se('No %s have been uploaded', '<span data-text="queue-objects">' . _n('file', 'files', 1) . '</span>');?></div>
						<div class="upload-box-status-text"><?php _se("Some errors have occurred and the system couldn't process your request."); ?></div>
					</div>
				</div>
            </div>
			<input id="anywhere-upload-input" data-action="anywhere-upload-input"<?php if (!getSetting('guest_uploads')) {
                                ?> data-login-needed="true"<?php
                            } ?> class="hidden-visibility" type="file" accept="image/*, <?php echo '.' . implode(',.', Image::getEnabledImageFormats()); ?>" multiple>
			<input id="anywhere-upload-input-camera" data-action="anywhere-upload-input"<?php if (!getSetting('guest_uploads')) {
                                ?> data-login-needed="true"<?php
                            } ?> class="hidden-visibility" type="file" capture="camera" accept="image/*">
			<ul id="anywhere-upload-queue" class="upload-box-queue content-width soft-hidden" data-group="upload-queue"></ul>
			<div id="anywhere-upload-submit" class="btn-container text-align-center margin-bottom-0 soft-hidden" data-group="upload-queue-ready">
				<div data-group="upload-queue-ready">
					<?php
                        if (Login::isLoggedUser() && Login::getUser()['album_count'] > 0) {
                            ?>
					<div class="input-label upload-input-col center-box text-align-left">
						<label for="upload-album-id"><?php _ne('Album', 'Albums', 1); ?></label>
						<select name="upload-album-id" id="upload-album-id" class="text-input">
						<?php
                            $user_album_options_html = [];
                            foreach ($user_albums as $album) {
                                $user_album_options_html[] = strtr('<option value="%id"%selected>%name</option>', [
                                            '%selected' => (Handler::var('album') !== [] && isset(Handler::var('album')['id_encoded']) && Handler::var('album')['id_encoded'] == $album['id_encoded']) ? ' selected' : '',
                                            '%id' => $album['id_encoded'],
                                            '%name' => $album['indent_string'] . $album['name_with_privacy_readable_html']
                                        ]);
                            }
                            $user_album_options_html = implode("\n", $user_album_options_html);
                            echo $user_album_options_html; ?>
						</select>
					</div>
					<?php
                        }
                    ?>
					<?php
                        if (Handler::var('categories')) {
                            ?>
					<div class="input-label upload-input-col center-box text-align-left">
						<label for="upload-category-id"><?php _se('Category'); ?></label>
						<select name="upload-category-id" id="upload-category-id" class="text-input">
							<option value><?php _se('Select %s', _s('category')); ?></option>
							<?php
                                foreach (Handler::var('categories') as $category) {
                                    ?>
							<option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
							<?php
                                } //for
                            ?>
						</select>
					</div>
					<?php
                        } // categories?
                    ?>
                    <?php if (getSetting('enable_expirable_uploads')) {
                        ?>
                <div class="input-label upload-input-col center-box text-align-left">
                    <label for="upload-expiration"><?php _se('Auto delete'); ?></label>
                    <select type="text" name="upload-expiration" id="upload-expiration" class="text-input">
                    <?php
                        $expirations = Image::getAvailableExpirations();
                        if (!Login::isLoggedUser() && getSetting('auto_delete_guest_uploads') != null) {
                            $expirations = [$expirations[getSetting('auto_delete_guest_uploads')]];
                        }
                        echo get_select_options_html($expirations, Login::isLoggedUser() ? Login::getUser()['image_expiration'] : null); ?>
                    </select>
					<?php
                        if (!Login::isLoggedUser() && getSetting('auto_delete_guest_uploads') != null) {
                            ?>
					<div class="input-below"><?php _se('%s to be able to customize or disable image auto delete.', '<a href="' . get_base_url('signup') . '">' . _s('Sign up') . '</a>'); ?></div>
					<?php
                        } ?>
                </div>
                <?php
                    } ?>
					<?php
                        if (getSetting('theme_nsfw_upload_checkbox') && !getSetting('enable_consent_screen')) {
                            ?>
					<div class="margin-20"><span rel="tooltip" data-tiptip="top" title="<?php _se('Mark this if the upload is not family safe'); ?>"><input type="checkbox" name="upload-nsfw" id="upload-nsfw" class="margin-right-5" value="1"><label for="upload-nsfw"><?php _se('Not family safe upload'); ?></label></span></div>
					<?php
                        }
                        if (!Login::isLoggedUser()) {
                            ?>
					<div class="margin-20"><input type="checkbox" name="upload-tos" id="upload-tos" class="margin-right-5" value="1"><label for="upload-tos"><?php echo Handler::var('tos_privacy_agreement'); ?></label></div>
					<?php
                        } ?>
					<button class="btn btn-big accent" data-action="upload" data-public="<?php _se('Upload'); ?>" data-private="<?php _se('Private upload'); ?>"><i class="fas fa-cloud-upload-alt"></i> <?php echo Handler::cond('forced_private_mode') ? _s('Private upload') : _s('Upload'); ?></button>
				</div>
				<div data-group="uploading" class="soft-hidden">
					<button class="btn plain disabled btn-big plain margin-right-5" disabled data-action="upload-privacy-copy"><span class="icon fas fa-lock-open" data-lock="fa-lock" data-unlock="fa-lock-open"></span></button><button class="btn btn-big disabled off" disabled><?php _se('Uploading'); ?></button> <span class="btn-alt"><?php _se('or'); ?> <a data-action="cancel-upload" data-button="close-cancel"><?php _se('cancel'); ?></a><a data-action="cancel-upload-remaining" data-button="close-cancel" class="soft-hidden"><?php _se('cancel remaining'); ?></a></span>
				</div>
			</div>
			<div id="anywhere-upload-report">
				<div data-group="upload-result" data-result="mixted" class="soft-hidden margin-top-10 text-align-center upload-box-status-text"><?php _se("Note: Some files couldn't be uploaded."); ?> <a data-modal="simple" data-target="failed-upload-result"><?php _se('learn more'); ?></a></div>
				<div data-group="upload-result" data-result="error" class="soft-hidden margin-top-10 text-align-center upload-box-status-text"><?php _se('Check the <a data-modal="simple" data-target="failed-upload-result">error report</a> for more information.'); ?></div>
			</div>
			<div class="upload-box-close position-absolute">
				<a class="btn btn-small default" data-action="reset-upload" data-button="close-cancel"><span class="btn-icon fas fa-undo"></span><span class="btn-text"><?php _se('reset'); ?></span></a>
				<a class="btn btn-small default" data-action="cancel-upload" data-button="close-cancel"><span class="btn-icon fas fa-times"></span><span class="btn-text"><?php _se('cancel'); ?></span></a>
				<a class="btn btn-small default" data-action="cancel-upload-remaining" data-button="close-cancel"><span class="btn-icon fas fa-times"></span><span class="btn-text"><?php _se('cancel remaining'); ?></span></a>
			</div>
			<?php if (getSetting('theme_show_embed_uploader')) {
                            ?>
			<div data-group="upload-result" data-result="success" class="c18 center-box soft-hidden">
				<div class="input-label margin-bottom-0">
                    <?php
                        if (Handler::cond('moderate_uploads')) {
                            ?>
                    <div class="growl font-size-small static text-align-center margin-bottom-30 clear-both"><b><?php _se('Note'); ?>:</b> <?php _se('We must approve the uploaded content before being able to share.'); ?></div>
                    <?php
                        } ?>
					<label for="uploaded-embed-toggle"><?php _se('Embed codes'); ?></label>
					<div class="c8 margin-bottom-10">
						<select name="uploaded-embed-toggle" id="uploaded-embed-toggle" class="text-input" data-combo="uploaded-embed-toggle-combo">
                            <?php
                                $tpl = Handler::cond('moderate_uploads') ? 'embed_unapproved_tpl' : 'embed_upload_tpl';
                            foreach (get_global($tpl) as $key => $value) {
                                echo '<optgroup label="' . $value['label'] . '">' . "\n";
                                foreach ($value['options'] as $k => $v) {
                                    echo '	<option value="' . $k . '" data-size="' . $v["size"] . '">' . $v["label"] . '</option>' . "\n";
                                }
                                echo '</optgroup>';
                            } ?>
						</select>
					</div>
					<div id="uploaded-embed-toggle-combo">
						<?php
                            $i = 0;
                            foreach (get_global('embed_upload_tpl') as $key => $value) {
                                foreach ($value['options'] as $k => $v) {
                                    echo '<div data-combo-value="' . $k . '" class="switch-combo' . ($i > 0 ? " soft-hidden" : "") . '">
										<textarea id="uploaded-embed-code-' . $i . '" class="r2 resize-vertical" name="' . $k . '" data-size="' . $v["size"] . '" data-focus="select-all"></textarea>
										<button type="button" class="input-action" data-action="copy" data-action-target="#uploaded-embed-code-' . $i . '"><i class="far fa-copy"></i> ' . _s('copy') . '</button>
										<button class="input-action" data-action="openerPostMessage" data-action-target="#uploaded-embed-code-' . $i . '">' . _s('insert') . '</button>
									</div>' . "\n";
                                    $i++;
                                }
                            } ?>
					</div>
				</div>
			</div>
			<?php
                        } ?>
        </div>
    </div>
	<div class="hidden">
		<div id="anywhere-upload-item-template">
			<li class="queue-item">
				<a class="block image-link" data-group="image-link" href="#file" target="_blank"></a>
				<div class="result done block"><span class="icon fas fa-check-circle color-white"></span></div>
				<div class="result failed block"><span class="icon fas fa-exclamation-triangle color-fail"></span></div>
				<div class="load-url block"><span class="big-icon fas fa-network-wired"></span></div>
				<div class="preview block checkered-background"></div>
				<div class="progress block">
					<div class="progress-bar" data-content="progress-bar"></div>
				</div>
				<div class="block edit" data-action="edit" title="<?php _se('Edit'); ?>">
				</div>
				<div class="queue-item-button cancel hover-display" data-action="cancel" title="<?php _se('Remove'); ?>">
					<span class="icon fas fa-times"></span>
				</div>
			</li>
		</div>
		<div id="anywhere-upload-edit-item">
			<span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _s('upload')); ?></span>
			<div class="modal-form">
				<div class="image-preview"></div>
				<div class="input-label">
					<label for="form-title"><?php _se('Title'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
					<input type="text" id="form-title" name="form-title" class="text-input" value="" maxlength="<?php echo getSetting('image_title_max_length'); ?>">
				</div>
				<?php
                    if (Login::isLoggedUser() && Login::getUser()['album_count'] > 0) {
                        ?>
				<div class="input-label c8">
					<label for="form-album-id"><?php _ne('Album', 'Albums', 1); ?></label>
					<select name="form-album-id" id="form-album-id" class="text-input">
						<?php echo $user_album_options_html ?? ''; ?>
					</select>
				</div>
				<?php
                    }
                ?>
				<?php
                    if (Handler::var('categories')) {
                        ?>
				<div class="input-label c8">
					<?php include_theme_file('snippets/form_category'); ?>
				</div>
				<?php
                    }
                ?>
				<div class="input-label" data-action="resize-combo-input">
					<label for="form-width" class="display-block-forced"><?php _se('Resize image'); ?></label>
					<div class="c6 overflow-auto clear-both">
						<div class="c3 float-left">
							<input type="number" min="16" pattern="\d+" name="form-width" id="form-width" class="text-input" title="<?php _se('Width'); ?>" rel="template-tooltip" data-tiptip="top">
						</div>
						<div class="c3 float-left margin-left-10">
							<input type="number" min="16" pattern="\d+" name="form-height" id="form-height" class="text-input" title="<?php _se('Height'); ?>" rel="template-tooltip" data-tiptip="top">
						</div>
					</div>
					<div class="input-below font-size-small" data-content="animated-gif-warning"><?php _se("Note: Animated GIF images won't be resized."); ?></div>
				</div>
                <?php if (getSetting('enable_expirable_uploads')) {
                    ?>
                <div class="input-label">
                    <label for="form-expiration"><?php _se('Auto delete'); ?></label>
                    <div class="c6 phablet-1">
                        <select type="text" name="form-expiration" id="form-expiration" class="text-input">
                        <?php
                            $expirations = Image::getAvailableExpirations();
                    if (!Login::isLoggedUser() && getSetting('auto_delete_guest_uploads') != null) {
                        $expirations = [$expirations[getSetting('auto_delete_guest_uploads')]];
                    }
                    echo get_select_options_html($expirations, Login::isLoggedUser() ? Login::getUser()['image_expiration'] : null); ?>
                        </select>
                    </div>
					<?php
                        if (!Login::isLoggedUser() && getSetting('auto_delete_guest_uploads') != null) {
                            ?>
					<div class="input-below"><?php _se('%s to be able to customize or disable image auto delete.', '<a href="' . get_base_url('signup') . '">' . _s('Sign up') . '</a>'); ?></div>
					<?php
                        } ?>
                </div>
                <?php
                } ?>
                <?php if (getSetting('theme_nsfw_upload_checkbox')) {
                    ?>
				<div class="checkbox-label">
					<div class="display-inline" rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Mark this if the image is not family safe'); ?>">
						<label for="form-nsfw">
							<input class="float-left" type="checkbox" name="form-nsfw" id="form-nsfw" value="1"><?php _se('Flag as unsafe'); ?>
						</label>
					</div>
				</div>
                <?php
                } ?>
				<div class="input-label">
					<label for="form-description"><?php _se('Description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
					<textarea id="form-description" name="form-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this %s', _n('image', 'images', 1)); ?>"></textarea>
				</div>
			</div>
		</div>
        <?php if (getSetting('enable_uploads_url')) { ?>
		<div id="anywhere-upload-paste-url" data-submit-fn="CHV.fn.uploader.pasteURL">
			<span class="modal-box-title"><?php echo $iconUrl; _se('Add image URLs'); ?></span>
			<div class="modal-form">
				<textarea class="resize-vertical" placeholder="<?php _se('Add the image URLs here'); ?>" name="urls"></textarea>
			</div>
		</div>
        <?php } ?>
	</div>
	<?php
        global $new_album, $user_items_editor;
        $new_album = true;
        $user_items_editor = [
            "user_albums" => $user_albums ?? null,
            "type" => "albums"
        ];
    ?>
	<div data-modal="form-uploaded-create-album" class="hidden" data-is-xhr data-submit-fn="CHV.fn.submit_upload_edit" data-ajax-deferred="CHV.fn.complete_upload_edit">
		<span class="modal-box-title"><i class="fas fa-images"></i> <?php _se('Create %s', _n('album', 'albums', 1)); ?></span>
		<p><?php
            _se('The uploaded content will be moved to this newly created album.');
            echo ' ';
            if (!Login::isLoggedUser()) {
                _se("You must %s or %l if you want to edit this album later on.", [
                    '%s' => '<a href="' . get_base_url("signup") . '">' . $iconSignup . _s('create an account') . '</a>',
                    '%l' => '<a href="' . get_base_url("login") . '">' . $iconSignin . _s('sign in') . '</a>'
                ]);
            }
        ?></p>
		<div class="modal-form">
			<?php
                if (Login::isLoggedUser()) {
                    ?>
			<div name="move-existing-album" id="move-existing-album" data-view="switchable" class="c7 input-label soft-hidden">
				<?php include_theme_file("snippets/form_move_existing_album"); ?>
			</div>
			<?php
                }
            ?>
			<div name="move-new-album" id="move-new-album" data-content="form-new-album" data-view="switchable">
				<?php include_theme_file("snippets/form_album"); ?>
			</div>
		</div>
	</div>
	<?php
        if (Login::isLoggedUser()) {
            ?>
	<div data-modal="form-uploaded-move-album" class="hidden" data-is-xhr data-submit-fn="CHV.fn.submit_upload_edit" data-ajax-deferred="CHV.fn.complete_upload_edit">
		<span class="modal-box-title"><i class="fas fa-exchange-alt"></i> <?php _se('Move to %s', _n('album', 'albums', 1)); ?></span>
		<p><?php _se('Select an existing album to move the uploaded content.'); ?></p>
		<div class="modal-form">
			<div name="move-existing-album" id="move-existing-album" data-view="switchable" class="c7 input-label">
				<?php include_theme_file("snippets/form_move_existing_album"); ?>
			</div>
			<div name="move-new-album" id="move-new-album" data-content="form-new-album" data-view="switchable" class="soft-hidden">
				<?php include_theme_file("snippets/form_album"); ?>
			</div>
		</div>
	</div>
	<?php
        }
    ?>
	<div data-modal="failed-upload-result" class="hidden">
		<span class="modal-box-title"><i class="fas fa-exclamation-circle"></i> <?php _se('Error report'); ?></span>
		<ul data-content="failed-upload-result" style="max-height: 115px;" class="overflow-auto"></ul>
	</div>
</div>
