<?php

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\get_chevereto_version;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<h1><i class="far fa-check-circle"></i> Update complete</h1>
<p><?php echo strtr('Chevereto has been successfully updated to %s. You can now continue to the <a href="%u">admin dashboard</a> and configure your website.', ['%s' => get_chevereto_version(true), '%u' => get_base_url('dashboard')]); ?></p>
<div>
    <a href="<?php echo get_base_url('dashboard'); ?>" class="action button radius">Dashboard</a>
    <a href="<?php echo get_base_url(); ?>" class="button radius">Website</a>
</div>
<script>$(document).ready(function() { CHV.fn.system.checkUpdates(); });</script>
