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
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (env()['CHEVERETO_CONTEXT'] === 'saas') {
    $about = _s('This service is based on Chevereto hosted edition.')
        . ' ' . _s('Usage of this service must be in compliance with the Chevereto Software Terms of Service.');
    $liability = _s("This instance is hosted in a service layer provided by Chevereto Software, which hereby declare not being responsible for the use of this service neither the damages that this service may cause.");
} else {
    $about = _s('This service is based on Chevereto self-hosted edition.')
        . ' ' . _s('Usage of Chevereto Software must be in compliance with the software license terms known as "The Chevereto License".');
    $liability = _s("This instance is hosted in a service layer not provided by Chevereto Software, which hereby declare to do not have any control nor access to the management layer of this instance and it won't be responsible for this service neither the damages that this service may cause.");
}

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
    font-size: 90%;
}
.powered-by--vendor a {
    color: inherit;
}
.powered-by--fineprint {
    font-size: 75% !important;
    text-align: justify;
    text-transform: uppercase;
    opacity: 0.7;
}
</style>
<div class="powered-by powered-by--vendor">
    <div class="display-inline-block margin-left-auto margin-right-auto"><a href="https://chevereto.com" target="_blank" rel="nofollow"><img src="<?php echo absolute_to_url(PATH_PUBLIC_CONTENT_LEGACY_SYSTEM . 'chevereto-blue.svg'); ?>" alt="" width="212"></a></div>
    <p><a href="https://chevereto.com/" target="_blank" class="btn btn-small default text-transform-uppercase"><span class="btn-icon fa-btn-icon fas fa-power-off"></span> chevereto.com</a></p>
    <div class="powered-by--fineprint c12 phone-c1 phablet-c1">
        <p><?php echo $about; ?></p>
        <p><?php echo $liability ?></p>
    </div>
</div>
