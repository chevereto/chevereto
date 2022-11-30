<?php
// @phpstan-ignore-next-line
// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<h1>Update failed</h1>
<p>The update process failed. Here is the error returned:</p>
<p class="highlight padding-10"><?php echo $error_message ?? ''; ?></p>
<p>If you update from an older version update your MyISAM tables to InnoDB table storage engine and try again.</p>
<p>If you have altered your database you will need to manually perform this update.</p>
