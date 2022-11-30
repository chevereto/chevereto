<?php

use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php $user_items_editor = Handler::var('user_items_editor') ?? get_global('user_items_editor'); ?>
<label for="form-category-id"><?php _se('Category'); ?></label>
<select name="form-category-id" id="form-category-id" class="text-input">
	<?php
        $categories = Handler::var('categories');
        array_unshift($categories, [
            'id' => null,
            'name' => _s('Select category'),
            'url_key' => null,
            'url' => null
        ]);
        foreach ($categories as $category) {
            ?>
	<option value="<?php echo $category['id']; ?>"<?php if ($category['id'] == ($user_items_editor['category_id'] ?? null)) {
                echo " selected";
            } ?>><?php echo $category['name']; ?></option>
	<?php
        }
    ?>
</select>
