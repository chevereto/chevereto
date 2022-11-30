<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div class="c24 phablet-c1">
    <div class="input-label margin-bottom-0">
        <label for="two_factor"><?php _se('Two-factor authentication'); ?></label>
        <?php if (Handler::cond('two_factor_enabled')) { ?>
            <p><i class="fas fa-check-circle color-accent"></i> <?php _se('Two-factor authentication is enabled.'); ?></p>
            <div class="margin-top-5">
                <a class="btn btn-small default" data-confirm="<?php _se("Do you really want to disable two-factor authentication?"); ?>" data-submit-fn="CHV.fn.user_two_factor.delete.submit" data-ajax-deferred="CHV.fn.user_two_factor.delete.deferred"><span class="icon fas fa-ban"></span><span class="margin-left-5"><?php _se('Disable'); ?></span></a>
            </div>
        <?php } else { ?>
        <p><?php _se('Scan the QR code below with an authenticator application and enter the code displayed.'); ?></p>
        <div class="margin-top-20 c8 phablet-c2 phone-c1">
            <div class="phone-text-align-center">
                <img src="<?php echo Handler::var('totp_qr_image'); ?>" alt="" class="qr col-8-max">
            </div>
            <div class="actions">
                <div class="btn-container margin-bottom-0">
                    <form data-content="main-form" class="overflow-auto" method="post" data-type="<?php echo Handler::var('setting'); ?>" data-action="validate">
                        <div class="input-label">
                            <label for="two-factor-code">OTP</label>
                            <input placeholder="123456" type="number" min="16" pattern="\d+" name="two-factor-code" id="two-factor-code" class="text-input" max="999999" required>
                            <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["two-factor-code"] ?? ''; ?></span>
                        </div>
                        <div class="margin-top-5">
                            <button type="submit" class="btn btn-small default"><span class="icon fas fa-check-circle"></span><span class="margin-left-5"><?php _se('Submit'); ?></span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
