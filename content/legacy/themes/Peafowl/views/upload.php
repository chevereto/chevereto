<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php include_theme_header(); ?>
<?php if (Handler::var('post') and Handler::cond('error')) { ?>
<script>
$(function() {
	PF.fn.growl.call("<?php echo Handler::var('error'); ?>");
});
</script>
<?php } ?>
<?php include_theme_footer(); ?>
