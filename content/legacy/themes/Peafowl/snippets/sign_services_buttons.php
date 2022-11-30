<?php
use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\get_base_url;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$i = 0;
$providersEnabled = Login::getProviders('enabled');
foreach ($providersEnabled as $name => $provider) {
    $attr = '';
    if ($i == 0) {
        $attr = ' class="margin-left-0"';
    }
    if ($i == count($providersEnabled) - 1) {
        $attr = ' class="margin-left-0 margin-right-0"';
    }
    $i++; ?><li<?php echo $attr; ?>><a class="sign-service btn-<?php echo $name; ?>" href="<?php echo get_base_url('connect/' . $name); ?>"><span class="btn-icon fab fa-<?php echo $name; ?>"></span><span class="btn-text phone-hide phablet-hide"><?php echo $provider['label']; ?></span></a></li><?php
} ?>
