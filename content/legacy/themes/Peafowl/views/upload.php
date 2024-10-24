<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_header(); ?>
<?php if (Handler::var('post') and Handler::cond('error')) { ?>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	PF.fn.growl.call("<?php echo Handler::var('error'); ?>");
});
</script>
<?php } ?>
<?php require_theme_footer(); ?>
