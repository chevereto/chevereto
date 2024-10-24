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
$tagsCount = count(Handler::var('tags'));
$tagsIds = implode(',', array_column(Handler::var('tags'), 'id'));
$tagAttribute = '';
$tagArgs = '';
if($tagsCount === 1 && Handler::cond('content_manager')) {
    $tagAttribute = ' data-tag-id="' . $tagsIds . '"';
    $tagArgs = ' data-args="' . $tagsIds . '"';
}
$tagsDescriptions = array_column(Handler::var('tags'), 'description');
$tagsDescriptions = array_filter($tagsDescriptions);
?>
<div class="content-width margin-bottom-10" data-content="tag"<?php echo $tagAttribute; ?>>
    <div class="header margin-bottom-10 margin-top-10 clearfix">
        <div class="header-content-left">
            <h1 class="header-title">
            <?php foreach(Handler::var('tags') as $tag) { ?>
            <a href="<?php echo $tag['url']; ?>" class="btn btn-tag default" data-content="tag-name" data-link="tag-url"><?php echo $tag['name_safe_html']; ?></a>
            <?php } ?>
            </h1>
        </div>
        <div class="header-content-right breaks-ui buttons">
<?php
if($tagsCount === 1 && Handler::cond('content_manager')) {
?>
            <a data-action="edit" title="<?php _se('Edit'); ?> (E)" class="btn btn-small default" data-modal="edit" data-target="form-modal"><span class="icon fas fa-edit"></span></a>
            <a data-action="delete" title="<?php _se('Delete'); ?> (Del)" class="btn btn-small default" <?php echo $tagAttribute . ' ' . $tagArgs; ?> data-confirm="<?php _se("Do you really want to delete this %s?", _s('tag')); ?> <?php _se("This can't be undone."); ?>" data-submit-fn="CHV.fn.tag.delete.submit" data-before-fn="CHV.fn.tag.delete.before" data-ajax-deferred="CHV.fn.tag.delete.complete"><span class="icon fas fa-trash-alt"></span></a>
<?php
}
?>
        </div>
    </div>
    <div data-content="tag-description"><?php echo safe_html(Handler::var('tags_descriptions')); ?></div>
</div>
<div class="top-sub-bar follow-scroll margin-bottom-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
            <h2 class="header-title">
                <i class="header-icon fas fa-tag<?php echo $tagsCount > 1 ? 's' : ''; ?> color-accent"></i>
                <span class="phone-hide"><?php echo _n('Tag', 'Tags', $tagsCount); ?></span>
            </h2>
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
if($tagAttribute !== '') {
    $tag = Handler::var('tags')[0];
    $tags = [
        $tag['id'] => $tag
    ];
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    CHV.obj.tags = <?php echo json_encode($tags); ?>;
});
</script>
<div data-modal="form-modal" class="hidden" data-submit-fn="CHV.fn.tag.edit.submit" data-before-fn="CHV.fn.tag.edit.before" data-ajax-deferred="CHV.fn.tag.edit.complete" data-ajax-url="<?php echo get_base_url('json'); ?>">
    <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _s('tag')); ?></span>
    <div class="modal-form">
        <input type="hidden" name="form-tag-id">
        <?php require_theme_file('snippets/form_tag_edit'); ?>
    </div>
</div>
<?php
}
?>
<?php require_theme_footer(); ?>
