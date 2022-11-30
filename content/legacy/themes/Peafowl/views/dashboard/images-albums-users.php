<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
Handler::setVar('tabs', Handler::var('sub_tabs'));
?>
<div id="content-listing-tabs" class="tabbed-listing">
    <div id="tabbed-content-group">
        <?php
            include_theme_file('snippets/listing');
        ?>
    </div>
</div>
