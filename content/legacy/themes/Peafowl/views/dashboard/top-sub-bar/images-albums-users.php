<?php

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
Handler::setVar('tabs', Handler::var('sub_tabs'));
?>
<div class="header header-tabs no-select">
    <?php include_theme_file('snippets/tabs'); ?>
    <?php
        Handler::setVar('user_items_editor', false);
        include_theme_file('snippets/user_items_editor');
    ?>
    <div class="header-content-right">
        <?php include_theme_file('snippets/listing_tools_editor'); ?>
    </div>
    <?php if (Handler::var('dashboard') == 'users') {
        ?>
        <div class="header-content-right">
            <div class="header--height header--centering">
                <a class="btn btn-small default" data-modal="form" data-target="modal-add-user"><i class="fas fa-user-plus margin-right-5"></i><?php _se('Add user'); ?></a>
            </div>
        </div>
        <div data-modal="modal-add-user" class="hidden" data-submit-fn="CHV.fn.user.add.submit" data-ajax-deferred="CHV.fn.user.add.complete">
            <span class="modal-box-title"><i class="fas fa-user-plus"></i> <?php _se('Add user'); ?></span>
            <div class="modal-form">
                <div class="input-label c8">
                    <label for="form-role"><?php _se('Role'); ?></label>
                    <select name="form-role" id="form-role" class="text-input">
                        <option value="user" selected><?php _se('User'); ?></option>
                        <option value="manager"><?php _se('Manager'); ?></option>
                        <option value="admin"><?php _se('Administrator'); ?></option>
                    </select>
                </div>
                <div class="input-label c11">
                    <label for="username"><?php _se('Username'); ?></label>
                    <input type="text" name="form-username" id="form-username" class="text-input" maxlength="<?php echo Settings::get('username_max_length'); ?>" rel="tooltip" data-tipTip="right" pattern="<?php echo Settings::get('username_pattern'); ?>" rel="tooltip" data-title='<?php echo strtr('%i to %f characters<br>Letters, numbers and "_"', ['%i' => Settings::get('username_min_length'), '%f' => Settings::get('username_max_length')]); ?>' maxlength="<?php echo Settings::get('username_max_length'); ?>" placeholder="<?php _se('Username'); ?>" required>
                    <span class="input-warning red-warning"></span>
                </div>
                <div class="input-label c11">
                    <label for="form-name"><?php _se('Email'); ?></label>
                    <input type="email" name="form-email" id="form-email" class="text-input" placeholder="<?php _se('Email address'); ?>" required>
                    <span class="input-warning red-warning"></span>
                </div>
                <div class="input-label c11">
                    <label for="form-name"><?php _se('Password'); ?></label>
                    <input type="password" name="form-password" id="form-password" class="text-input" title="<?php _se('%d characters min', Settings::get('user_password_min_length')); ?>" pattern="<?php echo Settings::get('user_password_pattern'); ?>" rel="tooltip" data-tipTip="right" placeholder="<?php _se('Password'); ?>" required>
                    <span class="input-warning red-warning"></span>
                </div>
            </div>
        </div>
    <?php
    } ?>
</div>
