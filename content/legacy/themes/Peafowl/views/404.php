<?php
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;
use function Chevereto\Vars\request;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_header(); ?>
<div class="content-width">
	<div class="page-not-found">
		<h1><?php _se("That page doesn't exist"); ?></h1>
		<p><?php _se('The requested page was not found.'); ?></p>
    </div>
</div>
<?php if (isset(request()["deleted"])) {
    ?>
<script>
	$(function() {
		PF.fn.growl.call("<?php echo get_route_name() == 'user' ? _s('The user has been deleted') : _s('The content has been deleted.'); ?>");
	});
</script>
<?php
} ?>
<?php include_theme_footer(); ?>
