<?php
use function Chevereto\Legacy\G\get_base_url;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<h1><i class="far fa-check-circle"></i> Already installed</h1>
<p>Chevereto is already installed and updated.</p>
<div>
    <a href="<?php echo get_base_url('dashboard'); ?>" class="action button radius"><span class="btn-icon fa-btn-icon fas fa-tachometer-alt"></span> Dashboard</a>
    <a href="<?php echo get_base_url(); ?>" class="button radius"><span class="btn-icon fa-btn-icon fas fa-globe"></span> Website</a>
</div>
