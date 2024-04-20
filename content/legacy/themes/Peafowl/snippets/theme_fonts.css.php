<?php

use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$fonts = Handler::var('fonts') ?? [];
?>
<style>
<?php
foreach ($fonts->get() as $id => $font) {
    echo <<<CSS
    .font-{$id}, html.font-{$id} body {
        font-family: {$font[0]};
    }

    CSS;
}
?>
</style>
