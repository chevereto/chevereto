<?php

use function Chevereto\Legacy\G\require_theme_file;

use Chevereto\Legacy\Classes\AssetStorage;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$storages = [
    'assets' => AssetStorage::getStorage(),
];
echo read_the_docs_settings('asset-storage', _s('Asset storage')); ?>
<div class="input-label"><i class="fas fa-info-circle margin-right-5"></i><?php _se('%s refers to website and user assets like background covers and avatars.', _s('Asset storage')); ?></div>
<div class="c24">
    <?php require_theme_file('snippets/form_storage_edit'); ?>
</div>
<script>
document.addEventListener("DOMContentLoaded",
    function() {
        var removals = [
            "input#form-storage-name",
            "input#form-storage-capacity",
            "input#storage_type_enable_image",
        ];
        $.each(removals, function(i, v){
            $(v).closest('.input-label').remove();
        });
        CHV.obj.storages = <?php echo json_encode($storages) ?: []; ?>;
        CHV.fn.storage.edit.before("assets");
        setTimeout(function() {
            $("#form-storage-api_id").trigger("change");
        }, 1);
    }
);
</script>
