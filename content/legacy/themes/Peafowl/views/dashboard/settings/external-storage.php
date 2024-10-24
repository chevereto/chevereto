<?php

use Chevereto\Legacy\Classes\Storage;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Vars\env;
use function Safe\json_encode;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('external-storage', _s('External storage')); ?>
<div class="input-label"><i class="fas fa-info-circle margin-right-5"></i><?php _se('%s refers to storage used for user file uploads.', _s('External storage')); ?></div>
<?php

$getStorages = Storage::get();
$storages = [];
foreach ($getStorages as $k => $v) {
    $storages[$v['id']] = $v;
}
$checkbox_icons = [
    0 => 'far fa-square',
    1 => 'far fa-check-square',
];
$storage_messages = [
    'is_https' => _s('Toggle this to enable or disable HTTPS'),
    'is_active' => _s('Toggle this to enable or disable this storage'),
];
$icon_template = '<span rel="toolTip" data-tipTip="right" title="%TITLE%" class="cursor-pointer icon %ICON%" data-checked-icon="' . $checkbox_icons[1] . '" data-unchecked-icon="' . $checkbox_icons[0] . '" data-action="toggle-storage-%PROP%" data-checkbox></span>'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    CHV.obj.storages = <?php echo json_encode($storages) ?: []; ?>;
    CHV.obj.storageTemplate = <?php echo json_encode(['messages' => $storage_messages, 'icon' => $icon_template, 'checkboxes' => $checkbox_icons]); ?>;
});
</script>
<ul data-content="dashboard-storages-list" class="tabbed-content-list table-li margin-top-20 margin-bottom-20">
    <li class="table-li-header phone-hide">
        <span class="c1 display-table-cell padding-right-10">ID</span>
        <span class="c4 display-table-cell padding-right-10"><?php _se('Name'); ?></span>
        <span class="c4 display-table-cell padding-right-10">API</span>
        <span class="c6 display-table-cell padding-right-10"><?php _se('Quota'); ?></span>
        <span class="c2 display-table-cell padding-right-10"><?php _se('Active'); ?></span>
        <span class="c7 display-table-cell padding-right-10"></span>
    </li>
    <?php
                $li_template = '<li data-content="storage" data-storage-id="%ID%">
        <span class="c1 display-table-cell padding-right-10" data-content="storage-id">%ID%</span>
        <span class="c4 display-table-cell padding-right-10"><a data-modal="edit" data-target="form-modal" data-storage-id="%ID%" data-content="storage-name">%NAME%</a></span>
        <span class="c4 display-table-cell padding-right-10" data-content="storage-api_name">%API_NAME%</span>
        <span class="c6 display-table-cell padding-right-10" data-content="storage-usage_label">%USAGE_LABEL%</span>
        <span class="c2 display-table-cell padding-right-10" data-content="storage-active">%IS_ACTIVE%</span>
        <span class="c7 display-table-cell padding-right-10"><a class="btn btn-small default" href="'
        . get_base_url('search/images/?q=storage:%ID%')
        . '" target="_blank"><i class="fas fa-search margin-right-5"></i>'
        . _s('Search')
        . '</a> <a class="btn btn-small default" data-storage-id="%ID%" data-args="%ID%" data-confirm="'
        . _s("Do you really want to delete the External Storage %s and all the content stored there?")
        . ' '
        . _s("This can't be undone.")
        . '" data-submit-fn="CHV.fn.storage.delete.submit" data-before-fn="CHV.fn.storage.delete.before" data-ajax-deferred="CHV.fn.storage.delete.complete"><i class="fas fa-trash-alt margin-right-5"></i>' . _s('Delete') . '</a></span>
    </li>';
                if ($storages) {
                    foreach ($storages as $storage) {
                        $replaces = [];
                        foreach ($storage as $k => $v) {
                            if (in_array($k, ['is_https', 'is_active'])) {
                                $v = strtr($icon_template, ['%TITLE%' => $storage_messages[$k], '%ICON%' => $checkbox_icons[(int) $v], '%PROP%' => str_replace('is_', '', $k)]);
                            }
                            $replaces['%' . strtoupper($k) . '%'] = $v;
                        }
                        echo strtr($li_template, $replaces);
                    }
                } ?>
</ul>
<div data-modal="form-modal" class="hidden" data-submit-fn="CHV.fn.storage.edit.submit" data-before-fn="CHV.fn.storage.edit.before" data-ajax-deferred="CHV.fn.storage.edit.complete" data-ajax-url="<?php echo get_base_url('json'); ?>">
    <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _s('storage')); ?></span>
    <div class="modal-form">
        <input type="hidden" name="form-storage-id">
        <?php require_theme_file('snippets/form_storage_edit'); ?>
    </div>
</div>
<div class="hidden" data-content="storage-dashboard-template">
    <?php echo $li_template; ?>
</div>
<?php if((bool) env()['CHEVERETO_ENABLE_LOCAL_STORAGE']) { ?>
<div class="font-weight-bold margin-bottom-5">
    <span class="c6 display-table-cell padding-right-10"><?php _se('Storage method'); ?></span>
    <span class="c3 display-table-cell"><?php _se('Disk used'); ?></span>
</div>
<?php foreach (Handler::var('storage_usage') as $k => $v) { ?>
<div class="margin-bottom-5">
    <span class="c6 display-table-cell padding-right-10"><?php echo $v['label']; ?></span>
    <span class="c3 display-table-cell padding-right-10"><?php echo $v['formatted_size']; ?></span>
<?php if ($k == 'all') { ?>
</div>
<?php continue; } ?>
    <span class="c6 display-table-cell"><?php echo $v['link']; ?></span>
</div>
<?php } ?>
<hr class="line-separator">
<p><i class="fas fa-info-circle"></i> <?php echo _s('Application local filesystem is used by default or when no external storage is active.'); ?></p>
<?php } ?>
