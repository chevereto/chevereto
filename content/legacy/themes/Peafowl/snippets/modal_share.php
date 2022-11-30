<?php

use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php $share_links_array = Handler::hasVar('share_links_array') ? Handler::var('share_links_array') : get_global("share_links_array"); ?>
<div id="modal-share" class="hidden">
	<span class="modal-box-title"><i class="fas fa-share-alt"></i> <?php _se('Share'); ?></span>
    <div class="image-preview"></div>
    <p class="highlight margin-bottom-20 font-size-small text-align-center padding-5" data-content="privacy-private">__privacy_notes__</p>
	<ul class="panel-share-networks">
		<?php echo join("", $share_links_array ?? []); ?>
	</ul>
	<div class="input-label margin-bottom-0">
        <label for="modal-share-url"><?php _se('Link'); ?></label>
        <div class="position-relative">
            <input type="text" name="modal-share-url" id="modal-share-url" class="text-input" value="__url__" data-focus="select-all" readonly>
            <button type="button" class="input-action" data-action="copy" data-action-target="#modal-share-url" value=""><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
        </div>
    </div>
</div>
