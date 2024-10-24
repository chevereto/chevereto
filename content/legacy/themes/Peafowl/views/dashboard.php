<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\require_theme_file;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
function read_the_docs_settings($key, $subject)
{
    return '<div class="growl static inline font-size-small">' . _s('Learn about %s at %t %d.', [
        '%t' => 'Chevereto',
        '%s' => '<i class="' . Handler::var('settings')['icon'] . '"></i> <b>' . $subject . '</b>',
        '%d' => get_admin_docs_link('settings/' . $key . '.html', _s('documentation')),
    ]) . '</div>';
}
function get_admin_docs_link($key, $subject)
{
    return '<a rel="external" href="' . Handler::var('adminDocsBaseUrl') . $key . '" target="_blank">' . $subject . '</a>';
}
function follow_sub_header(): bool
{
    return in_array(Handler::var('dashboard'), ['settings', 'files', 'albums', 'users', 'tags']);
} ?>
<?php require_theme_header(); ?>
<div class="top-sub-bar top-sub-bar--1<?php if (!follow_sub_header()) { ?> follow-scroll<?php } ?>">
    <div class="content-width">
        <div class="header header-tabs no-select">
            <h1 class="header-title">
                <span class="phone-hide header-icon fas fa-tachometer-alt"></span>
                <?php _se('Dashboard'); ?>
            </h1>
			<?php require_theme_file('snippets/tabs'); ?>
		</div>
    </div>
</div>
<?php if (follow_sub_header()) { ?>
<div class="top-sub-bar top-sub-bar--2 follow-scroll margin-bottom-5">
    <div class="content-width">
        <?php
        switch (Handler::var('dashboard')) {
            case 'settings':
                require 'dashboard/top-sub-bar/settings.php';

                break;
            case 'files':
            case 'albums':
            case 'tags':
            case 'users':
                require 'dashboard/top-sub-bar/images-albums-users.php';

                break;
        } ?>
    </div>
</div>
<?php } ?>
<div class="content-width">
	<div class="<?php echo Handler::cond('show_submit') ? 'form-content' : ''; ?>">
		<?php
        switch (Handler::var('dashboard')) {
            case 'stats':
                require 'dashboard/stats.php';

                break;
            case 'bulk-importer':
                require 'dashboard/bulk-importer.php';

                break;
            case 'files':
            case 'albums':
            case 'tags':
            case 'users':
                require 'dashboard/images-albums-users.php';

                break;
            case 'settings':
                require 'dashboard/settings.php';

                break;
        }
?>
</div>
</div>
<?php if (Handler::cond('error')) { ?>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
    PF.fn.growl.call("<?php echo Handler::var('error_message') ?? _s('Check the errors to proceed.'); ?>");
});
</script>
<?php }
require_theme_footer(); ?>
