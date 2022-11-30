<?php
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_header(); ?>
<div class="content-width">
	<div class="c24 center-box margin-top-40 margin-bottom-40">
        <div class="header default-margin-bottom">
            <h1 class="header-title">Example page</h1>
        </div>
        <div class="text-content">
            <p>This is an example page for your Chevereto site.</p>
			<h2>Creating and editing pages</h2>
			<p>To learn how add or modify a page go to our <a rel="external" href="https://v4-admin.chevereto.com/dashboard/pages.html" target="_blank">Pages documentation</a>.</p>
			<p><a href="https://v4-admin.chevereto.com/dashboard/pages.html" class="btn btn-small default" target="_blank"><span class="btn-icon fas fa-book"></span> Documentation</a></p>
		</div>
	</div>
</div>
<?php include_theme_footer(); ?>
