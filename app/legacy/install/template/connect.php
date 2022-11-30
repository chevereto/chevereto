<?php

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<h1><i class="fa fa-database"></i> Database connection</h1>
<p>To continue, provide your Chevereto MySQL database details.</p>
<?php if ($error ?? false) { ?>
<p class="highlight padding-10"><?php echo $error_message ?? ''; ?></p>
<?php } ?>
<form method="post" autocomplete="off">
	<div class="p input-label">
		<label for="db_host">Database host</label>
		<input autocomplete="off" type="text" name="db_host" id="db_host" class="width-100p" placeholder="localhost" title="Database server host (default: localhost)" required>
	</div>
    <div class="p input-label">
		<label for="db_port">Database port</label>
		<input autocomplete="off" type="number" min="0" name="db_port" id="db_port" class="width-100p" placeholder="3306" title="Database server port (default: 3306)" required>
	</div>
	<div class="p input-label">
		<label for="db_name">Database name</label>
		<input autocomplete="off" type="text" name="db_name" id="db_name" class="width-100p" title="Name of the database where you want to install Chevereto" required>
	</div>
	<div class="p input-label">
		<label for="db_user">Database user</label>
		<input autocomplete="off" type="text" name="db_user" id="db_user" class="width-100p" title="User with access to the above database" required>
	</div>
	<div class="p input-label">
		<label for="db_pass">Database user password</label>
		<input autocomplete="off" type="password" name="db_pass" id="db_pass" class="width-100p" title="Password of the above user">
	</div>
	<div class="p input-label">
		<label for="db_tablePrefix">Database table prefix</label>
		<input autocomplete="off" type="text" name="db_tablePrefix" id="db_tablePrefix" class="width-100p" placeholder="chv_" title="Database table prefix. Use chv_ if you don't need this">
	</div>
	<div>
		<button class="action radius" type="submit">Connect</button>
	</div>
</form>
