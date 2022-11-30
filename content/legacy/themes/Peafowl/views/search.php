<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_header(); ?>
<div class="content-width">
    <div class="header margin-top-20 margin-bottom-20">
        <h1><i class="header-icon fas fa-search color-accent"></i> <strong><?php _se('Search results for %s', '<em><b>' . Handler::var('safe_html_search')["d"] . '</b></em>'); ?></strong></h1>
    </div>
</div>
<div class="top-sub-bar follow-scroll margin-bottom-5 margin-top-5">
    <div class="content-width">
        <div class="header header-tabs no-select">
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
