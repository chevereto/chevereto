<?php

use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<script data-cfasync="false">
    var divLoading = document.createElement("div");
    var panelThumbs = document.querySelector('.panel-thumbs');
    var panelThumbList = document.querySelectorAll('.panel-thumb-list > li');
	document.addEventListener('DOMContentLoaded', function(event) {
		CHV.obj.image_viewer.image = {
			width: <?php echo Handler::var('image')["width"]; ?>,
			height: <?php echo Handler::var('image')["height"]; ?>,
			ratio: <?php echo number_format((float) (Handler::var('image')['ratio'] ?? 1), 6, '.', ''); ?>,
			url: "<?php echo Handler::var('image')["url"]; ?>",
			medium: {
				url: "<?php echo Handler::var('image')["medium"]["url"] ?? ''; ?>"
			},
            display_url: "<?php echo Handler::var('image')["display_url"]; ?>",
            url_viewer: "<?php echo Handler::var('image')["url_viewer"]; ?>",
            path_viewer: "<?php echo Handler::var('image')["path_viewer"]; ?>",
            is_360: <?php echo Handler::var('image')["is_360"] ? 'true' : 'false'; ?>,
		};
		CHV.obj.image_viewer.album = {
			id_encoded: "<?php echo Handler::var('image')["album"]["id_encoded"] ?? ''; ?>"
		};
	});
</script>
