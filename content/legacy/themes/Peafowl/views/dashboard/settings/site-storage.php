<?php

use function Chevereto\Legacy\G\require_theme_file;

use Chevereto\Legacy\Classes\AssetStorage;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$storages = [
    'assets' => AssetStorage::getStorage(),
];
echo read_the_docs_settings('site-storage', _s('Site storage')); ?>
<div class="input-label">
    <i class="fas fa-info-circle margin-right-5"></i>
    <?php _se('This is the storage for website and user assets, such as background images, covers, and avatars.'); ?>
</div>
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
        <?php if (Handler::var('input_errors')) { ?>
        var postData = <?php echo json_encode(Handler::var('safe_post') ?? []); ?>;
        if (postData && postData['form-storage-api_id']) {
            $('#form-storage-api_id').val(postData['form-storage-api_id']);
            CHV.fn.storage.prepareForm(postData['form-storage-api_id'], false);
            $.each(postData, function(key, value) {
                if (key.indexOf('form-storage-') === 0) {
                    var $field = $('[name="' + key + '"]');
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', value == 1).attr('checked', value == 1);
                    } else if ($field.is('select')) {
                        $field.val(value);
                        $('option', $field).removeAttr('selected');
                        $('option[value="' + value + '"]', $field).attr('selected', 'selected');
                    } else {
                        $field.val(value).attr('value', value);
                    }
                }
            });
        }
        <?php } else { ?>
        CHV.fn.storage.edit.before("assets");
        <?php } ?>
        setTimeout(function() {
            $("#form-storage-api_id").trigger("change");
        }, 1);
    }
);
</script>
