<?php
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<div data-modal="new-album" class="hidden" data-is-xhr data-submit-fn="CHV.fn.submit_create_album" data-ajax-deferred="CHV.fn.complete_create_album">
	<span class="modal-box-title"><i class="fas fa-images"></i> <?php _se('Create new album'); ?></span>
	<div class="modal-form">
	<?php include_theme_file("snippets/form_album.php", ['album-switch' => false]); ?>
	</div>
</div>
