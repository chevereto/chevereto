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
	<div class="list-tool tool-flag" data-action="flag" title="<?php _se('Toggle unsafe flag'); ?>">
        <span class="btn-icon far fa-flag label-flag-unsafe"></span>
		<span class="btn-icon fas fa-flag label-flag-safe"></span>
	</div>
    <?php
    }
    if (Handler::getRouteName() == 'moderate') {
        ?>
    <div class="list-tool tool-approve" data-action="approve" title="<?php _se('Approve'); ?>">
		<span class="btn-icon fas fa-check"></span>
	</div>
    <?php
    }
    ?>
	<div class="list-tool list-tool phone-hide" data-action="delete" title="<?php _se('Delete'); ?>">
		<span class="btn-icon fas fa-trash-alt"></span>
	</div>
</div>
