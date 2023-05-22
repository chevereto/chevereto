<?php
use Chevereto\Legacy\G\Handler;

?>
<div class="list-item-image-tools" data-action="list-tools">
	<div class="list-tool tool-select" data-action="select" title="<?php _se('Select'); ?>">
		<span data-icon-selected="fa-check-square" data-icon-unselected="fa-square" class="btn-icon far fa-square"></span>
	</div>
	<div class="list-tool tool-edit" data-action="edit">
		<span class="btn-icon fas fa-edit"></span><span class="label label-edit"><?php _se('Edit'); ?></span>
	</div>
	<div class="list-tool tool-move" data-action="move" title="<?php _se('Move to %s', _n('album', 'albums', 1)); ?>">
		<span class="btn-icon fas fa-exchange-alt"></span>
	</div>
	<?php
        if (Handler::cond('allowed_to_delete_content')) {
            ?>
	<div class="list-tool tool-delete" data-action="delete" title="<?php _se('Delete'); ?>">
		<span class="btn-icon fas fa-trash-alt"></span>
	</div>
	<?php
        }
    ?>
</div>
