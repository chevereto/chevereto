<?php

use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (! defined('ACCESS') || ! ACCESS) {
    exit('This file cannot be directly accessed.');
}
$version_patch = APP_VERSION;
$explode = explode('.', $version_patch);
$version_major = $explode[0];
$version_minor = $version_major . '.' . $explode[1];
$version_link = strtr(
    'https://releases.chevereto.com/%major%.X/%minor%/%patch%.html',
    [
        '%major%' => $version_major,
        '%minor%' => $version_minor,
        '%patch%' => $version_patch,
    ]
);
$edition = ucfirst(env()['CHEVERETO_EDITION'] ?? '');
?>
<h1><i class="fa fa-box-open"></i> Chevereto <?php echo $edition; ?> <a class="label--version" target="_blank" href="<?php echo $version_link; ?>"><?php echo APP_VERSION; ?></a></h1>
<p>Fill this form with the details of the initial admin account you want to use. You can change this account later on.</p>
<?php if ($error ?? false) { ?>
<p class="highlight padding-10"><?php echo $error_message ?? ''; ?></p>
<?php } ?>
<form method="post">
	<div>
        <div class="p input-label">
            <label for="username">Admin username</label>
            <input type="text" name="username" id="username" class="width-100p" value="<?php echo $safe_post['username'] ?? ''; ?>" placeholder="Admin username" pattern="<?php echo Settings::USERNAME_PATTERN; ?>"
            title="<?php echo strtr(
                <<<PLAIN
                %i to %f characters
                Letters, numbers and "_"
                PLAIN,
                [
                    '%i' => Settings::USERNAME_MIN_LENGTH,
                    '%f' => Settings::USERNAME_MAX_LENGTH,
                ]); ?>" maxlength="<?php echo Settings::USERNAME_MAX_LENGTH; ?>" required>
            <span class="input-warning red-warning"><?php echo $input_errors['username'] ?? ''; ?></span>
        </div>
        <div class="p input-label">
            <label for="email">Admin email</label>
            <input type="email" name="email" id="email" class="width-100p" value="<?php echo $safe_post['email'] ?? ''; ?>" placeholder="Admin email" title="Valid email address for your admin account" required>
            <span class="input-warning red-warning"><?php echo $input_errors['email'] ?? ''; ?></span>
        </div>
        <div class="p input-label input-password">
            <label for="password">Admin password</label>
            <input type="password" name="password" id="password" class="width-100p" value="" placeholder="Admin password" title="Password to login" pattern="<?php echo Settings::USER_PASSWORD_PATTERN; ?>" autocomplete="new-password" required>
            <div class="input-password-strength"><span style="width: 0%" data-content="password-meter-bar"></span></div>
            <div class="input-warning red-warning" data-text="password-meter-message"><?php echo $input_errors['password'] ?? ''; ?></div>
        </div>
    </div>
	<?php
        if ($is_2X ?? false) {
            ?>
    <div>
        <div class="p input-label">
            <label for="crypt_salt">__CHV_CRYPT_SALT__</label>
            <input type="text" name="crypt_salt" id="crypt_salt" class="width-100p" value="<?php echo $safe_post['crypt_salt'] ?? ''; ?>" placeholder="Example: changeme" title="As defined in includes/definitions.php" required>
            <span class="input-below highlight">Value from define("__CHV_CRYPT_SALT__", "changeme");</span>
            <span class="input-warning red-warning"><?php echo $input_errors['crypt_salt'] ?? ''; ?></span>
        </div>
    </div>
	<?php
        }
?>
	<div>
		<button class="action radius" type="submit">Install</button>
	</div>
</form>
