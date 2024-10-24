<?php
use function Chevereto\Legacy\G\get_route_name;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;
use function Chevereto\Vars\request;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_header(); ?>
<div class="content-width">
	<div class="page-not-found">
		<h1><?php _se("That page doesn't exist"); ?></h1>
		<p><?php _se('The requested page was not found.'); ?></p>
    </div>
</div>
<?php if (isset(request()["deleted"])) {
    ?>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	PF.fn.growl.call("<?php echo get_route_name() == 'user' ? _s('The %s has been deleted', _s('user')) : _s('The %s has been deleted.', _s('content')); ?>");
});
</script>
<?php
} ?>
<?php require_theme_footer(); ?>
