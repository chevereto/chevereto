<?php

use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<?php
$owner = Handler::var('owner') ?? get_global("owner");
?>
<div class="breadcrum-item pop-btn pop-btn-auto pop-keep-click pop-btn-desktop margin-right-0">
	<a href="<?php echo $owner['url']; ?>" class="user-image">
		<?php if (isset($owner['avatar']['url'])) {
    ?>
		<img class="user-image" src="<?php echo $owner['avatar']['url']; ?>" alt="<?php echo $owner['username']; ?>">
		<?php
} else {
        ?>
		<span class="user-image default-user-image"><span class="icon fas fa-user-circle"></span></span>
		<?php
    } ?>
	</a>
</div>
