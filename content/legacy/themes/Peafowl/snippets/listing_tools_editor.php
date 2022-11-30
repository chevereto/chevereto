<?php

use function Chevereto\Legacy\G\get_global;
use function Chevereto\Legacy\G\get_route_name;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<?php
$listing = Handler::hasVar('listing') ? Handler::var('listing') : get_global('listing');
$tabs = (array) (get_global('tabs') ? get_global('tabs') : Handler::var('tabs'));
foreach ($tabs as $tab) {
    if ((isset($tab['list']) && $tab['list'] === false) || isset($tab['tools']) && $tab['tools'] === false) {
        continue;
    } ?>
<div data-content="list-selection" data-tab="<?php echo $tab['id']; ?>" class="header--height header--centering list-selection <?php $class = [];
    if (isset($listing) && (is_array($listing->output) == false || count($listing->output) == 0)) {
        $class[] = 'disabled';
    }
    if (!$tab['current']) {
        $class[] = 'hidden';
    }
    echo implode(' ', $class); ?>">
	<div class="display-inline-block user-select-none"><a data-action="list-select-all" class="header-link" data-text-select-all="<?php _se('All'); ?>" data-text-clear-all="<?php _se('Clear'); ?>"><?php _se('All'); ?></a></div>

	<div data-content="pop-selection" class="disabled sort-listing pop-btn header-link user-select-none display-inline-block">
		<span class="selection-count user-select-none" data-text="selection-count"></span><span class="pop-btn-text no-select margin-left-5" data-content="label"><span class="icon far fa-check-square margin-right-5"></span><?php _se('Actions'); ?></span>
		<div class="pop-box anchor-right arrow-box arrow-box-top">
			<div class="pop-box-inner pop-box-menu">
				<ul>
					<?php
                        if ($tab['type'] == 'images') {
                            ?>
					<li class="with-icon"><a data-action="get-embed-codes"><span class="btn-icon fas fa-code"></span><?php _se('Get embed codes'); ?><kbd>K</kbd></a></li>
					<?php
                        } ?>
					<?php
                        if (in_array(get_route_name(), ['user', 'album']) and (array_key_exists('tools_available', $tab) ? in_array('album', $tab['tools_available']) : true)) {
                            ?>
					<li class="with-icon"><a data-action="create-album"><span class="btn-icon fas fa-images"></span><?php _se('Create album'); ?><kbd>A</kbd></a></li>
					<li class="with-icon"><a data-action="move"><span class="btn-icon fas fa-exchange-alt"></span><?php _se('Move to album'); ?><kbd>M</kbd></a></li>
					<?php
                        } ?>
                    <?php
                        if ($tab['type'] == 'images') {
                            ?>
					<?php
                        if ((array_key_exists('tools_available', $tab) ? in_array('category', $tab['tools_available']) : true) and Handler::var('categories')) {
                            ?>
					<li class="with-icon"><a data-action="assign-category"><span class="btn-icon fas fa-columns"></span><?php _se('Assign category'); ?><kbd>C</kbd></a></li>
					<?php
                        } ?>
					<?php
                        if (Handler::cond('allowed_nsfw_flagging') && (array_key_exists('tools_available', $tab) ? (in_array('flag', $tab['tools_available'])) : true)) {
                            ?>
					<li class="with-icon"><a data-action="flag-safe" class="hidden"><span class="btn-icon far fa-flag"></span><?php _se('Flag as safe'); ?><kbd>V</kbd></a></li>
					<li class="with-icon"><a data-action="flag-unsafe" class="hidden"><span class="btn-icon fas fa-flag"></span><?php _se('Flag as unsafe'); ?><kbd>F</kbd></a></li>
					<?php
                        }
                            if (Handler::getRouteName() == 'moderate') { ?>
                    <li class="with-icon"><a data-action="approve"><span class="btn-icon fas fa-check-double"></span><?php _se('Approve'); ?><kbd>O</kbd></a></li>
                    <?php
                            }
                        } // images?>
                    <?php
                        if (Handler::cond('allowed_to_delete_content') && (array_key_exists('tools_available', $tab) ? in_array('delete', $tab['tools_available']) : true)) {
                            ?>
					<li class="with-icon"><a data-action="delete"><span class="btn-icon fas fa-trash-alt"></span><?php _se('Delete'); ?><kbd>Del</kbd></a></li>
					<?php
                        } ?>
                    <div class="or-separator margin-top-5 margin-bottom-5"></div>
                    <li class="with-icon"><a data-action="list-select-all"><span class="btn-icon fas fa-check-square"></span><?php _se('Select all'); ?><kbd>.</kbd></a></li>
                    <li class="with-icon"><a data-action="clear"><span class="btn-icon fas fa-times-circle"></span><?php _se('Clear selection'); ?><kbd>Z</kbd></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
