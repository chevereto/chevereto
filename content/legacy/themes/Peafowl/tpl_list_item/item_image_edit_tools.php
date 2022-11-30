<?php
use Chevereto\Legacy\G\Handler;

?>
<div class="list-item-image-tools" data-action="list-tools">
	<div class="list-tool tool-select" data-action="select">
		<span data-icon-selected="fa-check-square" data-icon-unselected="fa-square" class="btn-icon far fa-square" title="<?php _se('Select'); ?>"></span>
	</div>
    <?php
    if (Handler::cond('allowed_nsfw_flagging')) {
        ?>
	<div class="list-tool tool-flag phone-hide">
		<span data-action="flag" class="btn-icon far fa-flag label-flag-unsafe" title="<?php _se('Toggle unsafe flag'); ?>"></span>
		<span data-action="flag" class="btn-icon fas fa-flag label-flag-safe" title="<?php _se('Toggle unsafe flag'); ?>"></span>
	</div>
    <?php
    }
    ?>
	<div class="list-tool tool-edit phone-hide" data-action="edit">
		<span class="btn-icon fas fa-edit" title="<?php _se('Edit'); ?>"></span>
	</div>
    <div class="list-tool tool-move phone-hide" data-action="move">
		<span class="btn-icon fas fa-exchange-alt" title="<?php _se('Move to album'); ?>"></span>
	</div>
	<div class="list-tool tool-create phone-hide" data-action="create-album">
		<span class="btn-icon fas fa-images" title="<?php _se('Create album'); ?>"></span>
	</div>
    <?php
        if (Handler::cond('allowed_to_delete_content')) {
            ?>
	<div class="list-tool tool-delete phone-hide" data-action="delete">
		<span class="btn-icon fas fa-trash-alt" title="<?php _se('Delete'); ?>"></span>
	</div>
	<?php
        }
    ?>
</div>
