<?php // @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<script>
var hasClass = function(element, cls) {
	return (" " + element.className + " ").indexOf(" " + cls + " ") > -1;
};
var top_bar = {
		node: document.getElementById("top-bar")
	},
	html = document.getElementsByTagName("html")[0];
</script>
