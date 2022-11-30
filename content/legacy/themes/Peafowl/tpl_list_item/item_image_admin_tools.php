<?php
use Chevereto\Legacy\G\Handler;

?>
<div class="list-item-image-tools" data-action="list-tools">
	<div class="tool-select" data-action="select">
		<span data-icon-selected="fa-check-square" data-icon-unselected="fa-square" class="btn-icon far fa-square" title="<?php _se('Select'); ?>"></span>
	</div>
    <?php
    if (Handler::cond('allowed_nsfw_flagging')) {
        ?>
	<div class="list-tool tool-flag">
        <span data-action="flag" class="btn-icon far fa-flag label-flag-unsafe" title="<?php _se('Toggle unsafe flag'); ?>"></span>
		<span data-action="flag" class="btn-icon fas fa-flag label-flag-safe" title="<?php _se('Toggle unsafe flag'); ?>"></span>
	</div>
    <?php
    }
    if (Handler::getRouteName() == 'moderate') {
        ?>
    <div class="list-tool tool-approve" data-action="approve">
		<span class="btn-icon fas fa-check" title="<?php _se('Approve'); ?>"></span>
	</div>
    <?php
    }
    ?>
	<div class="list-tool list-tool phone-hide" data-action="delete">
		<span class="btn-icon fas fa-trash-alt" title="<?php _se('Delete'); ?>"></span>
	</div>
</div>
