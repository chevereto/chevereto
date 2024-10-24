<?php

use Chevereto\Legacy\G\Handler;

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;
use function Chevereto\Legacy\G\safe_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
require_theme_header();
?>
<div class="content-width margin-bottom-10" data-content="category" data-category-id="<?php echo Handler::var('category')['id']; ?>">
    <div class="header margin-bottom-10 margin-top-10 clearfix">
        <div class="header-content-left">
            <h1 class="header-title">
                <a href="<?php echo Handler::var('category')['url']; ?>" data-content="category-name" data-link="category-url"><?php echo safe_html(Handler::var('category')['name']); ?></a>
            </h1>
        </div>
        <div class="header-content-right breaks-ui buttons">
<?php
if(Handler::cond('admin')) {
?>
            <a data-action="edit" title="<?php _se('Edit'); ?> (E)" class="btn btn-small default" data-modal="edit" data-target="form-modal" data-category-id="<?php echo Handler::var('category')['id']; ?>"><span class="icon fas fa-edit"></span></a>
            <a data-action="delete" title="<?php _se('Delete'); ?> (Del)" class="btn btn-small default" data-category-id="<?php echo Handler::var('category')['id']; ?>" data-args="<?php echo Handler::var('category')['id']; ?>" data-confirm="<?php _se("Do you really want to delete this %s?", _s('category')); ?> <?php _se("This can't be undone."); ?>" data-submit-fn="CHV.fn.category.delete.submit" data-before-fn="CHV.fn.category.delete.before" data-ajax-deferred="CHV.fn.category.delete.complete"><span class="icon fas fa-trash-alt"></span></a>
<?php
}
?>
        </div>
    </div>
    <div data-content="category-description"><?php echo safe_html(Handler::var('category')['description']); ?></div>
</div>
<div class="top-sub-bar follow-scroll margin-bottom-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
            <h2 class="header-title">
                <i class="header-icon fas fa-columns color-accent"></i>
                <span class="phone-hide"><?php _se('Category'); ?></span>
            </h2>
<?php if (Handler::var('list') !== null) { ?>
            <h1 class="header-title"><strong><?php echo '<span class="header-icon ' . Handler::var('list')['icon'] . '"></span><span class="phone-hide margin-left-5">' . Handler::var('list')['label']; ?></span></strong>
            </h1>
<?php } ?>
    	<?php require_theme_file("snippets/tabs"); ?>
		<?php
            if (Handler::cond('content_manager')) {
                require_theme_file("snippets/user_items_editor"); ?>
            <div class="header-content-right">
                <?php require_theme_file("snippets/listing_tools_editor"); ?>
            </div>
		<?php
            }
        ?>
        </div>
    </div>
</div>
<div class="content-width">
    <div id="content-listing-tabs" class="tabbed-listing">
        <div id="tabbed-content-group">
            <?php
                require_theme_file("snippets/listing");
            ?>
        </div>
    </div>
</div>
<?php
if(Handler::cond('admin')) {
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    CHV.obj.categories = <?php echo json_encode(Handler::var('categories')); ?>;
});
</script>
<div data-modal="form-modal" class="hidden" data-submit-fn="CHV.fn.category.edit.submit" data-before-fn="CHV.fn.category.edit.before" data-ajax-deferred="CHV.fn.category.edit.complete" data-ajax-url="<?php echo get_base_url('json'); ?>">
    <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _s('category')); ?></span>
    <div class="modal-form">
        <input type="hidden" name="form-category-id">
        <?php require_theme_file('snippets/form_category_edit'); ?>
    </div>
</div>
<?php
}
?>
<?php require_theme_footer(); ?>
