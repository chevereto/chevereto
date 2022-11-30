<?php
use Chevereto\Legacy\G\Handler;

?>
<div class="list-item-image-tools" data-action="list-tools">
	<div class="tool-select" data-action="select">
		<span data-icon-selected="fa-check-square" data-icon-unselected="fa-square" class="btn-icon far fa-square" title="<?php _se('Select'); ?>"></span>
	</div>
	<div class="list-tool tool-edit phone-hide" data-action="edit"><span class="btn-icon fas fa-edit"></span><span class="label label-edit"><?php _se('Edit'); ?></span></div>
	<div class="list-tool tool-move" data-action="move">
		<span class="btn-icon fas fa-exchange-alt" title="<?php _se('Move to album'); ?>"></span>
	</div>
	<?php
        if (Handler::cond('allowed_to_delete_content')) {
            ?>
	<div class="list-tool tool-delete" data-action="delete">
		<span class="btn-icon fas fa-trash-alt" title="<?php _se('Delete'); ?>"></span>
	</div>
	<?php
        }
    ?>
</div>
