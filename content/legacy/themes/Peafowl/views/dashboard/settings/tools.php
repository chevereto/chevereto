<?php

use function Chevereto\Legacy\badgePaid;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\inputDisabledPaid;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('tools', _s('Tools')); ?>
<div class="input-label">
    <label for="decode-id"><?php _se('Decode ID'); ?></label>
    <div class="phablet-c1">
        <input type="text" data-dashboard-tool="decodeId" name="decode-id" id="decode-id" class="c4 text-input" placeholder="<?php echo encodeID(1337); ?>"> <a class="btn btn-input default" data-action="dashboardTool" data-tool="decodeId" data-data='{"id":"#decode-id"}'><span class="loading display-inline-block"></span><span class="text"><?php _se('Decode ID'); ?></span></a>
    </div>
</div>
<div class="input-label">
    <label for="encode-id"><?php _se('Encode ID'); ?></label>
    <div class="phablet-c1">
        <input type="number" data-dashboard-tool="encodeId" min="0" name="encode-id" id="encode-id" class="c4 text-input" placeholder="1234"> <a class="btn btn-input default" data-action="dashboardTool" data-tool="encodeId" data-data='{"id":"#encode-id"}'><span class="loading display-inline-block"></span><span class="text"><?php _se('Encode ID'); ?></span></a>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="test-email"><?php _se('Send test email'); ?></label>
    <div class="phablet-c1">
        <input type="email" data-dashboard-tool="testEmail" name="test-email" id="test-email" class="c4 text-input" placeholder="test@mail.com"> <a class="btn btn-input default" data-action="dashboardTool" data-tool="testEmail" data-data='{"email":"#test-email"}'><span class="loading display-inline-block"></span><span class="text"><?php _se('Send test email'); ?></span></a>
    </div>
    <div class="input-below"><?php _se('Use this to test how your emails are being delivered. We recommend you to use %s.', '<a rel="external" href="https://www.mail-tester.com/" target="_blank">mail-tester</a>'); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="export-user"><?php _se('Export a user'); ?></label>
    <div class="phablet-c1">
        <input type="text" data-dashboard-tool="exportUser" name="export-user" id="export-user" class="c4 text-input" placeholder="<?php _se('Username'); ?>"> <a class="btn btn-input default" data-action="dashboardTool" data-tool="exportUser" data-data='{"username":"#export-user"}'><span class="loading display-inline-block"></span><span class="text"><?php _se('Export user'); ?></span></a>
    </div>
    <div class="input-below"><?php _se("This will allow you to download a user's standard personal information in JSON format."); ?></div>
</div>
<hr class="line-separator">
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="storageId"><?php _se('Regenerate external storage stats'); ?></label>
    <div class="phablet-c1">
        <input <?php echo inputDisabledPaid('pro'); ?> type="number" data-dashboard-tool="regenStorageStats" min="0" step="1" name="storageId" id="storageId" class="c4 text-input" placeholder="<?php _se('Storage id'); ?>"> <a class="btn btn-input default" data-action="dashboardTool" data-tool="regenStorageStats" data-data='{"storageId":"#storageId"}'><span class="loading display-inline-block"></span>
        <span class="text"><?php _se('Regenerate'); ?></span></a>
    </div>
    <div class="input-below"><?php _se('This will re-calculate the sum of all the image records associated to the target external storage.'); ?></div>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="sourceStorageId"><?php _se('Migrate external storage records'); ?></label>
    <div class="phablet-c1">
        <input <?php echo inputDisabledPaid('pro'); ?> type="number" data-dashboard-tool="migrateStorage" min="0" step="1" name="sourceStorageId" id="sourceStorageId" class="c5 text-input" placeholder="<?php _se('Source storage id'); ?>">
        <input <?php echo inputDisabledPaid('pro'); ?> type="number" data-dashboard-tool="migrateStorage" min="0" step="1" name="targetStorageId" id="targetStorageId" class="c5 text-input" placeholder="<?php _se('Target storage id'); ?>">
        <a class="btn btn-input default" data-action="dashboardTool" data-tool="migrateStorage" data-data='{"sourceStorageId":"#sourceStorageId", "targetStorageId":"#targetStorageId"}'>
            <span class="loading display-inline-block"></span>
            <span class="text"><?php _se('Migrate'); ?></span>
        </a>
    </div>
    <div class="input-below"><?php _se('This only updates the database. You must transfer the actual files to target storage container on your own. URL rewriting is strongly recommended. Use zero (0) for local storage.'); ?></div>
</div>
