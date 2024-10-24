<?php // @phpstan-ignore-next-line

use Chevereto\Legacy\Classes\Category;

if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<div class="input-label c8">
	 <label for="form-tag-name"><?php _se('Name'); ?></label>
	 <input type="text" id="form-tag-name" name="form-tag-name" class="text-input" value="" placeholder="<?php _se('%s name', _s('Tag')) ?>" required maxlength="32">
</div>
<div class="input-label">
	<label for="form-tag-description"><?php _se('Description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
	<textarea id="form-tag-description" name="form-tag-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this %s', _s('tag')); ?>"></textarea>
</div>
