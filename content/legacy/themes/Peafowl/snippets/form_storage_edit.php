<?php

use Chevereto\Legacy\Classes\Storage;
use Chevereto\Legacy\Classes\StorageApis;
use Chevereto\Legacy\Classes\Upload;
use Chevereto\Legacy\G\Handler;

use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div id="growl-placeholder"></div>
<div class="input-label">
	<label for="form-storage-api_id"><?php _se('API'); ?></label>
	<div class="c8">
		<select name="form-storage-api_id" id="form-storage-api_id" class="text-input" data-combo="storage-combo">
<?php
$pos = 0;
foreach (StorageApis::getEnabled() as $k => $v) {
	$pos++;
	echo strtr('<option value="%key" data-url="%url"%disabled' . ($pos == 1 ? ' selected' : '') . '>%name</option>', [
		'%key' => $k,
		'%url' => '',
		'%name' => (($v['disabled'] ?? false) ? '* ' : '') . $v['name'],
		'%disabled' => ($v['disabled'] ?? false) ? ' disabled' : null,
	]);
}
?>
		</select>
	</div>
<?php if(!(bool) env()['CHEVERETO_ENABLE_EXTERNAL_STORAGE_PROVIDERS']) { ?>
	<div class="input-below">(*) <?php _se('Not available in %s.', 'Chevereto ' . (string) env()['CHEVERETO_EDITION']); ?></div>
<?php } ?>
	<div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['form-storage-api_id'] ?? ''; ?></div>
</div>
<div class="input-label c8">
	<label for="form-storage-name"><?php _se('Name'); ?></label>
	<input type="text" id="form-storage-name" name="form-storage-name" class="text-input" placeholder="<?php _se('Storage name'); ?>" required maxlength="32">
</div>
<div id="storage-combo">
	<div data-combo-value="11" class="red-warning input-label switch-combo soft-hidden"><i class="fas fa-exclamation-triangle"></i> <?php _se('This is for the old deprecated B2 API. For new buckets you have to use S3 Compatible API.'); ?></div>
	<div data-combo-value="1 9" class="input-label c8 switch-combo">
		<label for="form-storage-use_path_style_endpoint"><input type="checkbox" name="form-storage-use_path_style_endpoint" id="form-storage-use_path_style_endpoint" value="1" data-checked="0"> <?php _se('Use path style endpoint'); ?></label>
	</div>
	<div data-combo-value="1" class="input-label c8 switch-combo">
		<label for="form-storage-region"><?php _se('Region'); ?></label>
		<select name="form-storage-region" id="form-storage-region" class=" text-input">
			<?php foreach (Storage::getAPIRegions('s3') as $k => $v) {
            ?>
			<option value="<?php echo $k; ?>" data-url="<?php echo $v['url']; ?>"><?php echo $v['name']; ?></option>
			<?php
        } ?>
		</select>
	</div>
	<div data-combo-value="9" class="input-label c8 switch-combo soft-hidden">
		<label for="form-storage-region"><?php _se('Region'); ?></label>
		<input type="text" id="form-storage-region" name="form-storage-region" class="text-input" placeholder="<?php _se('Storage region'); ?>" required>
	</div>
	<div data-combo-value="1 2 9 3 10 11" class="switch-combo">
		<div class="input-label c8">
			<label for="form-storage-bucket">Bucket</label>
			<input type="text" id="form-storage-bucket" name="form-storage-bucket" class="text-input" placeholder="<?php _se('Storage bucket'); ?>" required>
		</div>
	</div>
	<div data-combo-value="1 9 10" class="switch-combo">
		<div class="input-label c8">
			<label for="form-storage-key"><?php _se('Key'); ?></label>
			<input type="text" id="form-storage-key" name="form-storage-key" class="text-input" placeholder="<?php _se('Storage key'); ?>" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-secret"><?php _se('Secret'); ?></label>
			<input type="text" id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="<?php _se('Storage secret'); ?>" required>
		</div>
	</div>
	<div data-combo-value="11" class="switch-combo soft-hidden">

		<div class="input-label c8">
			<label for="form-storage-key"><?php _se('Key'); ?></label>
			<input type="text" id="form-storage-key" name="form-storage-key" class="text-input" placeholder="keyID" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-secret"><?php _se('Secret'); ?></label>
			<input type="text" id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="Master Application Key" required>
		</div>
	</div>
	<div data-combo-value="3" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-key"><?php _se('Account'); ?></label>
			<input type="text" id="form-storage-key" name="form-storage-key" class="text-input" placeholder="AccountName" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-secret"><?php _se('Key'); ?></label>
			<input type="text" id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="AccountKey" required>
		</div>
	</div>
	<div data-combo-value="2" class="switch-combo soft-hidden">
		<div class="input-label c15">
			<label for="form-storage-secret"><?php _se('Private key'); ?></label>
			<textarea id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="<?php _se('Google Cloud JSON key'); ?>" required></textarea>
			<div class="input-below"><?php _se('You will need a <a %s>service account</a> for this.', 'href="https://cloud.google.com/storage/docs/authentication#service_accounts" target="_blank"'); ?></div>
		</div>
	</div>
	<div data-combo-value="7" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-service"><?php _se('Service name'); ?></label>
			<input type="text" id="form-storage-service" name="form-storage-service" class="text-input" placeholder="swift">
		</div>
		<div class="input-label c8">
			<label for="form-storage-server"><?php _se('Identity URL'); ?></label>
			<input type="text" id="form-storage-server" name="form-storage-server" class="text-input" placeholder="<?php _se('Identity API endpoint'); ?>" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('API endpoint for OpenStack identity'); ?>">
		</div>
		<div class="input-label c8">
			<label for="form-storage-key"><?php _se('Username'); ?></label>
			<input type="text" id="form-storage-key" name="form-storage-key" class="text-input" placeholder="<?php _se('Username'); ?>" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-secret"><?php _se('Password'); ?></label>
			<input type="text" id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="<?php _se('Password'); ?>" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-region"><?php _se('Region'); ?></label>
			<input type="text" id="form-storage-region" name="form-storage-region" class="text-input" placeholder="<?php _se('Storage region'); ?>">
		</div>
		<div class="input-label c8">
			<label for="form-storage-bucket"><?php _se('Container'); ?></label>
			<input type="text" id="form-storage-bucket" name="form-storage-bucket" class="text-input" placeholder="<?php _se('Storage container'); ?>" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-account_id"><?php _se('Tenant id'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
			<input type="text" id="form-storage-account_id" name="form-storage-account_id" class="text-input" placeholder="<?php _se('Tenant id (account id)'); ?>">
		</div>
		<div class="input-label c8">
			<label for="form-storage-account_name"><?php _se('Tenant name'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
			<input type="text" id="form-storage-account_name" name="form-storage-account_name" class="text-input" placeholder="<?php _se('Tenant name (account name)'); ?>">
		</div>
	</div>
	<div data-combo-value="9" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-server">Endpoint</label>
			<input type="url" id="form-storage-server" name="form-storage-server" class="text-input" placeholder="Endpoint" pattern="https?://.+" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Storage endpoint'); ?>">
		</div>
	</div>
	<div data-combo-value="3" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-server">Endpoint <span class="optional"><?php _se('optional'); ?></span></label>
			<input type="url" id="form-storage-server" name="form-storage-server" class="text-input" placeholder="Endpoint" pattern="https?://.+" rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Storage endpoint'); ?>">
		</div>
	</div>
	<div data-combo-value="10" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-server">Endpoint</label>
			<input type="text" id="form-storage-server" name="form-storage-server" class="text-input" placeholder="Endpoint" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Storage endpoint'); ?>">
		</div>
	</div>
	<div data-combo-value="5 6" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-server"><?php _se('Server'); ?></label>
			<input type="text" id="form-storage-server" name="form-storage-server" class="text-input" placeholder="<?php _se('Server address'); ?>" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Hostname or IP of the storage server'); ?>">
		</div>
		<div class="input-label">
			<label for="form-storage-bucket"><?php _se('Path'); ?></label>
			<div class="c8">
				<input type="text" id="form-storage-bucket" name="form-storage-bucket" class="text-input" placeholder="<?php _se('Server path'); ?>" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Absolute path where the files will be stored in the context of the %p login. Use %s for root path.', [
                '%s' => '<code>&#47;</code>',
                '%p' => 'SFTP/FTP',
            ]); ?>">
			</div>
		</div>
		<div class="input-label c8">
			<label for="form-storage-key"><?php _ne('User', 'Users', 1); ?></label>
			<input type="text" id="form-storage-key" name="form-storage-key" class="text-input" placeholder="<?php _se('Server login'); ?>" required>
		</div>
		<div class="input-label c8">
			<label for="form-storage-secret"><?php _se('Password'); ?></label>
			<input type="text" id="form-storage-secret" name="form-storage-secret" class="text-input" placeholder="<?php _se('Server password'); ?>" required>
		</div>
	</div>
	<div data-combo-value="8" class="switch-combo soft-hidden">
		<div class="input-label c8">
			<label for="form-storage-bucket"><?php _se('Path'); ?></label>
			<input type="text" id="form-storage-bucket" name="form-storage-bucket" class="text-input" placeholder="<?php _se('Local path'); ?>" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Local path where the files will be stored'); ?>">
		</div>
	</div>
	<div class="input-label">
		<div class="c8">
			<label for="form-storage-capacity"><?php _se('Storage capacity'); ?></label>
			<input type="text" id="form-storage-capacity" name="form-storage-capacity" class="text-input" placeholder="<?php _se('Example: 20 GB, 1 TB, etc.'); ?>">
		</div>
		<div class="input-below"><?php _se('This storage will be disabled when it reach this capacity. Leave it blank or zero for no limit.'); ?></div>
	</div>
	<div class="input-label">
		<label for="form-storage-url">URL</label>
		<input type="text" id="form-storage-url" name="form-storage-url" class="text-input" placeholder="<?php _se('Storage URL'); ?>" value="<?php echo Storage::getAPIRegions('s3')['us-east-1']['url']; ?>" required>
		<div class="input-below"><?php _se('Map files in this storage under this URL.'); ?></div>
	</div>
	<div class="input-label">
		<label for="form-storage-types"><?php _se('Enabled types'); ?></label>
		<div class="checkbox-label">
			<ul class="c20 phablet-c1">
			<?php
                foreach (Upload::getAvailableTypes() as $k) {
                    echo strtr('<li class="c5 display-inline-block margin-right-10"><label class="display-block" for="storage_type_enable_%k" %tip> <input type="checkbox" name="storage_type_enable_%k" id="storage_type_enable_%k" value="%k" %checked %disabled>%K</label></li>', [
                        '%k' => $k,
                        '%K' => ucfirst($k),
                        '%checked' => 'checked data-checked="1"',
                        '%disabled' => '',
                        '%tip' => ''
                    ]);
                } ?>
			</ul>
			<p class="margin-top-20"><i class="fas fa-check-square"></i> <?php _se("The storage will be used only for selected types."); ?></p>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
	$(document).on("change", "select[name=form-storage-api_id]", function() {
		$(this)
			.data(
				"value",
				$("option:selected", this).prop("value")
			);
		CHV.fn.storage.prepareForm(
			$(this).data("value")
		);
	});
});
</script>
