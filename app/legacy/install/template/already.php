<?php
use function Chevereto\Legacy\G\get_base_url;

// @phpstan-ignore-next-line
if (! defined('ACCESS') || ! ACCESS) {
    exit('This file cannot be directly accessed.');
} ?>
<h1><i class="far fa-check-circle"></i> Already installed</h1>
<p>Chevereto is already installed and updated.</p>
<div>
    <a href="<?php echo get_base_url('dashboard'); ?>" class="action button radius"><span class="fas fa-tachometer-alt"></span> Dashboard</a>
    <a href="<?php echo get_base_url(); ?>" class="button radius"><span class="fas fa-globe"></span> Website</a>
</div>
