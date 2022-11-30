<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\time_elapsed_string;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$api_date = Handler::var('api_v1_date_created'); ?>
<div class="growl static inline font-size-small"><?php
    _se(
    'Learn about %s at our %d.',
    [
                '%s' => '<b><i class="fas fa-project-diagram"></i> API</b>',
                '%d' => '<a rel="external" href="https://v4-docs.chevereto.com/developer/api/api-v1.html" target="_blank">' . _s('documentation') . '</a>',
            ]
); ?></div>
<div class="input-label">
    <label for="api_v1_key"><?php _se('API key'); ?></label>
    <?php if (Handler::hasVar('api_v1_key')) { ?>
    <div class="input-below"><span class="highlight padding-5 display-inline-block"><i class="fas fa-exclamation-triangle"></i> <?php _se('Store the %s in a secure location as it will be shown just once.', _s('API key')); ?></span></div>
    <div class="c12 phablet-c1 position-relative margin-top-10">
        <input readonly type="text" data-focus="select-all" name="api_v1_key" id="api_v1_key" class="text-input" value="<?php echo Handler::var('api_v1_key'); ?>">
        <button type="button" class="input-action" data-action="copy" data-action-target="#api_v1_key"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
    </div>
    <?php } ?>
    <div class="margin-top-10">
        <p><?php echo Handler::var('api_v1_public_display'); ?></p>
        <?php echo '<i class="far fa-clock"></i> <span title="' . $api_date . '">' . time_elapsed_string($api_date) . '</span>'; ?>
    </div>
    <div class="margin-top-10">
        <button type="button" class="btn btn-small default" data-confirm="<?php _se("Are you sure that you want to revoke the existing API key?"); ?>" data-submit-fn="CHV.fn.user_api.delete.submit" data-ajax-deferred="CHV.fn.user_api.delete.deferred"><span class="icon fas fa-redo"></span><span class="margin-left-5"><?php _se('Regen key'); ?></span></button>
    </div>
</div>
