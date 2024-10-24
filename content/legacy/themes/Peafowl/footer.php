<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\is_route;
use function Chevereto\Legacy\G\require_theme_file_return;
use function Chevereto\Legacy\get_static_url;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\include_peafowl_foot;
use function Chevereto\Legacy\show_theme_inline_code;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (!Handler::cond('maintenance')) {
    require_theme_file('snippets/embed_tpl');
}
if (Handler::cond('upload_allowed') && (getSetting('upload_gui') == 'js' || is_route('upload'))) {
    require_theme_file('snippets/anywhere_upload');
}
if (getSetting('theme_show_social_share')) {
    require_theme_file("snippets/modal_share");
}
try {
    require_theme_file('custom_hooks/footer');
} catch (Throwable $e) {
}
include_peafowl_foot();
show_theme_inline_code('snippets/footer.js');
echo getSetting('analytics_code');
?>
<template id="tags-autocomplete-item">
<?php
$tag = require_theme_file_return('snippets/tag_autocomplete');
echo $tag('%name%', '');
?>
</template>
<?php if (Handler::cond('show_powered_by_footer')) { ?>
<div class="footer">
    <div class="content-width"><a href="https://chevereto.com" rel="generator" target="_blank"><?php _se('Powered by %s media sharing platform', '<img src="'.get_static_url(PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'chevereto-blue.svg').'" alt="Chevereto">'); ?></a></div>
</div>
<?php } ?>
</body>
</html>
