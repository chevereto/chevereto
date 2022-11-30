<?php
use function Chevereto\Legacy\G\get_base_url;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<h1>Edit app/env.php</h1>
<p>The database details are correct, but the system was unable to put these at the <code>app/env.php</code> file.</p>
<p>You will require to edit the <code><?php echo PATH_APP . 'env.php'; ?></code> file with the contents below. Once done, re-load this window.</p>
<code class="display-block" data-click="select-all"><pre><?php echo htmlentities($envDotPhpContents ?? ''); ?></pre></code>
<div>
    <a href="<?php echo get_base_url(); ?>" class="action button radius">Re-load</a>
</div>
