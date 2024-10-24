<?php
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\require_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<div id="form-modal" class="hidden" data-load-fn="CHV.fn.albumEdit.load" data-before-fn="CHV.fn.albumEdit.before" data-submit-fn="CHV.fn.albumEdit.submit" data-ajax-deferred="CHV.fn.albumEdit.complete" data-ajax-url="<?php echo get_base_url("json"); ?>">
    <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _n('album', 'albums', 1)); ?></span>
    <div class="modal-form">
        <?php require_theme_file('snippets/form_album', ['album-root' => true]); ?>
    </div>
</div>
