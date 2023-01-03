<?php
use Chevereto\Legacy\G\Handler;

?>
<div class="list-item-image-tools" data-action="list-tools">
	<div class="list-tool tool-select" data-action="select" title="<?php _se('Select'); ?>">
		<span data-icon-selected="fa-check-square" data-icon-unselected="fa-square" class="btn-icon far fa-square"></span>
	</div>
    <?php
    if (Handler::cond('allowed_nsfw_flagging')) {
        ?>
	<div class="list-tool tool-flag phone-hide" title="<?php _se('Toggle unsafe flag'); ?>" data-action="flag">
		<span class="btn-icon far fa-flag label-flag-unsafe"></span>
		<span class="btn-icon fas fa-flag label-flag-safe"></span>
	</div>
    <?php
    }
    ?>
	<div class="list-tool tool-edit" data-action="edit" title="<?php _se('Edit'); ?>">
		<span class="btn-icon fas fa-edit"></span>
	</div>
    <div class="list-tool tool-move phone-hide" data-action="move" title="<?php _se('Move to %s', _s('album')); ?>">
		<span class="btn-icon fas fa-exchange-alt"></span>
	</div>
	<div class="list-tool tool-create phone-hide" data-action="create-album" title="<?php _se('Create %s', _s('album')); ?>">
		<span class="btn-icon fas fa-images"></span>
	</div>
    <?php
        if (Handler::cond('allowed_to_delete_content')) {
            ?>
	<div class="list-tool tool-delete phone-hide" data-action="delete" title="<?php _se('Delete'); ?>">
		<span class="btn-icon fas fa-trash-alt"></span>
	</div>
	<?php
        }
    ?>
</div>
