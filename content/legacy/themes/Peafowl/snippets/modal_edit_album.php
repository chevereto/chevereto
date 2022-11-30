<?php
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<div id="form-modal" class="hidden" data-before-fn="CHV.fn.before_album_edit" data-submit-fn="CHV.fn.submit_album_edit" data-ajax-deferred="CHV.fn.complete_album_edit" data-ajax-url="<?php echo get_base_url("json"); ?>">
    <h1><?php _se('Edit'); ?></h1>
    <div class="modal-form">
        <?php include_theme_file('snippets/form_album'); ?>
    </div>
</div>
