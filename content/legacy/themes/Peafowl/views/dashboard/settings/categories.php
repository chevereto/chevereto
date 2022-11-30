<?php

use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\getSetting;
use function Safe\json_encode;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('categories', _s('Categories')); ?>
<?php if (!getSetting('website_explore_page')) { ?>
    <div class="growl static"><?php _se("Categories won't work when the explorer feature is turned off. To revert this setting go to %s.", ['%s' => '<a href="' . get_base_url('dashboard/settings/website') . '">' . _s('Dashboard > Settings > Website') . '</a>']); ?></div>
<?php
} ?>
<script>
    $(document).ready(function() {
        CHV.obj.categories = <?php echo json_encode(Handler::var('categories')); ?>;
    });
</script>
<ul data-content="dashboard-categories-list" class="tabbed-content-list table-li-hover table-li margin-top-20 margin-bottom-20">
    <li class="table-li-header phone-hide">
        <span class="c5 display-table-cell padding-right-10"><?php _se('Name'); ?></span>
        <span class="c4 display-table-cell padding-right-10 phone-hide phablet-hide"><?php _se('URL key'); ?></span>
        <span class="c13 display-table-cell phone-hide"><?php _se('Description'); ?></span>
    </li>
    <?php
    $li_template = '<li data-content="category" data-category-id="%ID%">
<span class="c5 display-table-cell padding-right-10"><a data-modal="edit" data-target="form-modal" data-category-id="%ID%" data-content="category-name">%NAME%</a></span>
<span class="c4 display-table-cell padding-right-10 phone-hide phablet-hide" data-content="category-url_key">%URL_KEY%</span>
<span class="c13 display-table-cell padding-right-10 phone-display-block" data-content="category-description">%DESCRIPTION%</span>
<span class="display-table-cell"><a class="btn btn-small default" data-category-id="%ID%" data-args="%ID%" data-confirm="' . _s("Do you really want to delete the %s category? This can't be undone.") . '" data-submit-fn="CHV.fn.category.delete.submit" data-before-fn="CHV.fn.category.delete.before" data-ajax-deferred="CHV.fn.category.delete.complete"><i class="fas fa-trash-alt margin-right-5"></i>' . _s('Delete') . '</a></span>
</li>';
if (Handler::var('categories')) {
    foreach (Handler::var('categories') as $category) {
        $replaces = [];
        foreach ($category as $k => $v) {
            $replaces['%' . strtoupper($k) . '%'] = $v;
        }
        echo strtr($li_template, $replaces);
    }
} ?>
</ul>
<div class="hidden" data-content="category-dashboard-template">
    <?php echo $li_template; ?>
</div>
<p><i class="fas fa-info-circle"></i> <?php _se("Note: Deleting a category doesn't delete the images that belongs to that category."); ?></p>
<div data-modal="form-modal" class="hidden" data-submit-fn="CHV.fn.category.edit.submit" data-before-fn="CHV.fn.category.edit.before" data-ajax-deferred="CHV.fn.category.edit.complete" data-ajax-url="<?php echo get_base_url('json'); ?>">
    <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit category'); ?></span>
    <div class="modal-form">
        <input type="hidden" name="form-category-id">
        <?php include_theme_file('snippets/form_category_edit'); ?>
    </div>
</div>
