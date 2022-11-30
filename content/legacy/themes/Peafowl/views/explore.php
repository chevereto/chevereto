<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\show_banner;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
include_theme_header();
if (Handler::var('category') !== null && isset(Handler::var('category')['name'])) {
    ?>
<div class="content-width">
    <div class="header margin-top-20 margin-bottom-10">
        <h1 class="header-title" rel="tooltip" data-tipTip="right" title="ID:<?php echo Handler::var('category')['id']; ?>"><i class="fas fa-columns color-accent"></i> <strong><?php _se('%s category', '<em><b>' . Handler::var('category')['name'] . '</b></em>'); ?></strong></h1>
    </div>
    <div><?php echo safe_html(Handler::var('category')['description']); ?></div>
</div>
<?php
} ?>
<div class="top-sub-bar follow-scroll margin-bottom-5 margin-top-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
<?php if (Handler::var('list') !== null) { ?>
            <h1 class="header-title"><strong><?php echo '<span class="header-icon ' . Handler::var('list')['icon'] . '"></span><span class="phone-hide margin-left-5">' . Handler::var('list')['label']; ?></span></strong>
            </h1>
<?php } ?>
    	<?php include_theme_file("snippets/tabs"); ?>
		<?php
            if (Handler::cond('content_manager')) {
                include_theme_file("snippets/user_items_editor"); ?>
            <div class="header-content-right">
                <?php include_theme_file("snippets/listing_tools_editor"); ?>
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
                include_theme_file("snippets/listing");
            ?>
        </div>
    </div>
</div>
<?php include_theme_footer(); ?>
