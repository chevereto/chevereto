<?php
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<script id="viewer-template" type="text/x-chv-template">
	<div class="viewer viewer--hide list-item" data-cover="1">
		<div class="viewer-content no-select">
			<img draggable="false" class="viewer-src no-select animate" src="%display_url%" alt="%filename%" width="%width%" height="%height%" data-media="%type%">
            <div id="image-viewer-360" class="soft-hidden"></div>
		</div>
		<div class="viewer-wheel phone-hide phablet-hide tablet-hide hover-display">
			<div class="viewer-wheel-prev animate" data-action="viewer-prev"><span class="icon fas fa-angle-left"></span></div>
			<div class="viewer-wheel-next animate" data-action="viewer-next"><span class="icon fas fa-angle-right"></span></div>
		</div>
		<ul class="viewer-tools list-item-image-tools hover-display idle-display no-select" data-action="list-tools">
			<div data-action="viewer-close" title="<?php _se('Close'); ?> (Esc)">
				<span class="btn-icon fas fa-times"></span>
			</div>
		</ul>
		<div class="viewer-top hover-display">
			<a href="%path_viewer%" class="glass-button" target="_blank">
				<i class="fa-solid fa-arrow-up-right-from-square margin-right-5"></i><span class="txt">%display_title%</span>
			</a>
		</div>
		<div class="viewer-foot hover-display hover-display--flex">
			<div class="viewer-owner viewer-owner--user">
				<a href="%user.url%" title="@%user.username%">
					<span class="user-image default-user-image"><span class="icon fas fa-user-circle"></span></span>
					<img class="user-image" src="%user.avatar.url%" alt="%user.username%">
				</a>
			</div>
			<div class="viewer-owner header-content buttons" data-contains="cta-album">
				%album.cta_html%
			</div>
		</div>
		<div class="list-item-privacy list-item-image-tools --top --left">
			<div class="btn-icon btn-lock fas fa-eye-slash"></div>
		</div>
        <div class="list-item-image-tools --bottom --right">
        <?php
            if (getSetting('enable_likes')) {
                ?>
            <div class="list-item-like" data-action="like" title="<?php _se('Like'); ?> (L)">
                <span class="btn-icon btn-like btn-liked fas fa-heart"></span>
                <span class="btn-icon btn-like btn-unliked far fa-heart"></span>
            </div>
		<?php
            } ?>
		<?php
            if (getSetting('theme_show_social_share')) {
                ?>
            <div class="list-item-share" data-action="share" title="<?php _se('Share'); ?> (S)">
                <span class="btn-icon btn-share fas fa-share-alt"></span>
            </div>
        <?php
            } ?>
        </div>
	</div>
</script>
