<?php // @phpstan-ignore-next-line

use Chevereto\Legacy\Classes\Category;

if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>

<div class="input-label c8">
	 <label for="form-category-name"><?php _se('Name'); ?></label>
	 <input type="text" id="form-category-name" name="form-category-name" class="text-input" value="" placeholder="<?php _se('%s name', _s('Category')) ?>" required maxlength="32">
</div>
<div class="input-label c8">
	 <label for="form-category-url_key"><?php _se('URL key'); ?></label>
     <input type="text" id="form-category-url_key" name="form-category-url_key" class="text-input" value="" placeholder="<?php _se('%s URL key', _s('Category')) ?>" required rel="template-tooltip" data-tiptip="right" data-title="<?php _se('Only letters, numbers, and hyphens'); ?>" pattern="<?php echo Category::URL_KEY_PATTERN; ?>">
</div>
<div class="input-label">
	<label for="form-category-description"><?php _se('Description'); ?> <span class="optional"><?php _se('optional'); ?></span></label>
	<textarea id="form-category-description" name="form-category-description" class="text-input no-resize" placeholder="<?php _se('Brief description of this %s', _s('category')); ?>"></textarea>
</div>
