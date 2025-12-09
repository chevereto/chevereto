<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\G\absolute_to_url;
use function Chevereto\Legacy\getPoweredByRemarks;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
[$about, $liability, $content] = getPoweredByRemarks();
?>
<style>
.powered-by p {
    margin: 10px 0;
    line-height: 1.4;
}
.powered-by--provider img {
    max-width: 100%;
}
.powered-by--vendor {
    margin-top: 20px;
    font-size: 1em;
}
.powered-by--vendor a {
    color: inherit;
}
.powered-by--fine-print {
    font-size: 75% !important;
    text-align: justify;
    opacity: 0.7;
    text-transform: uppercase;
}
.powered-by--fine-print a {
    text-decoration: underline;
}
</style>
<div class="powered-by powered-by--vendor">
    <div class="display-inline-block margin-left-auto margin-right-auto"><a href="https://chevereto.com" target="_blank" rel="nofollow"><img src="<?php echo absolute_to_url(PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'chevereto-blue.svg'); ?>" alt="" width="212"></a></div>
    <p><a href="https://chevereto.com/" target="_blank" class="btn btn-small default text-transform-uppercase"><span class="fas fa-power-off"></span> chevereto.com</a></p>
    <div class="powered-by--fine-print c12 phone-c1 phablet-c1">
        <p><?php echo $about; ?></p>
        <p><?php echo $liability ?></p>
        <p><?php echo $content; ?></p>
    </div>
</div>
