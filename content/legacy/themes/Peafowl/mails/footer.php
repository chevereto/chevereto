<?php
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<br><br>--<br>
<?php
_se(
    'This email was sent from %w %u',
    [
        '%w' => getSetting('website_name', true),
        '%u' => get_public_url()
    ]
);
?>
</body>
</html>
