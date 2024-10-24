<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\show_banner;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
require_theme_header();
?>
<div class="top-sub-bar follow-scroll margin-bottom-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
<?php if (Handler::var('list') !== null) { ?>
            <h1 class="header-title"><strong><?php echo '<span class="header-icon ' . Handler::var('list')['icon'] . '"></span><span class="phone-hide margin-left-5">' . Handler::var('list')['label']; ?></span></strong>
            </h1>
<?php } ?>
    	<?php require_theme_file("snippets/tabs"); ?>
		<?php
            if (Handler::cond('content_manager')) {
                require_theme_file("snippets/user_items_editor"); ?>
            <div class="header-content-right">
                <?php require_theme_file("snippets/listing_tools_editor"); ?>
            </div>
		<?php
            }
        ?>
        </div>
    </div>
</div>
<?php show_banner('explore_after_top', Handler::var('listing')->sfw()); ?>
<div class="content-width">
    <div id="content-listing-tabs" class="tabbed-listing">
        <div id="tabbed-content-group">
            <?php
                require_theme_file("snippets/listing");
            ?>
        </div>
    </div>
</div>
<?php require_theme_footer(); ?>
