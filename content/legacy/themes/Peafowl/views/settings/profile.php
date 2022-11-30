<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_checkbox_html;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div class="c12 phablet-c1">
    <div class="input-upload user-settings-avatar">
        <div class="user-settings-avatar-container grid-columns margin-right-10 phablet-float-left">
            <?php if (isset(Handler::var('user')['avatar']['filename'])) { ?>
                <img src="<?php echo Handler::var('user')['avatar']['url']; ?>" alt="" class="user-image">
            <?php } else { ?>
                <img src="" alt="" class="user-image<?php echo !isset(Handler::var('user')['avatar']['filename']) ? ' hidden' : ''; ?>">
            <?php } ?>
            <span class="user-image default-user-image<?php echo isset(Handler::var('user')['avatar']['filename']) ? ' hidden' : ''; ?>"><span class="icon fas fa-user-circle"></span></span>
            <div class="user-image loading-placeholder hidden"><?php _se('loading'); ?></div>
        </div>
        <div class="actions">
            <div class="btn-container phone-text-align-center">
                <a class="btn btn-small default" data-trigger="user-avatar-upload"><span class="btn-icon fas fa-camera"></span><span class="btn-text"><?php _se('Upload avatar'); ?></span></a>
                <div class="margin-top-5<?php echo !isset(Handler::var('user')['avatar']['filename']) ? ' soft-hidden' : ''; ?>">
                    <a class="btn btn-small default" data-action="delete-avatar"><span class="icon fas fa-trash-alt"></span><span class="margin-left-5"><?php _se('Delete'); ?></span></a>
                </div>
            </div>
        </div>
        <input id="user-avatar-upload" data-content="user-avatar-upload-input" class="hidden-visibility" type="file" accept="image/*">
    </div>
</div>
<div class="input-label">
    <label><?php _se('Privacy'); ?></label>
    <?php echo get_checkbox_html([
            'name' => 'is_private',
            'label' => _s('Make my profile and identity totally private'),
            'tooltip' => _s('Enable this if you want to act like an anonymous user'),
            'checked' => ((bool) (Handler::var('safe_post')['is_private'] ?? Handler::var('user')['is_private']))
        ]); ?>
</div>
<div class="c12 phablet-c1">
    <div class="input-label">
        <label for="name"><?php _se('Name'); ?></label>
        <input type="text" name="name" id="name" class="text-input" maxlength="60" value="<?php echo Handler::var('safe_post')["name"] ?? Handler::var('safe_html_user')["name"]; ?>" placeholder="Your real name" required>
        <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["name"] ?? ''; ?></span>
        <?php if (!Handler::cond('dashboard_user')) {
            ?><div class="input-below"><?php _se('This is your real name, not your username.'); ?></div><?php
        } ?>
    </div>
    <div class="input-label">
        <label for="website"><?php _se('Website'); ?></label>
        <input type="url" name="website" id="website" class="text-input" value="<?php
            echo !empty(Handler::var('safe_post'))
                    ? (Handler::var('safe_post')["website"] ?? '')
                    : (Handler::var('user')["website_safe_html"] ?? ''); ?>" data-validate rel="tooltip" title="https://domain.loc" data-tipTip="right" placeholder="https://domain.loc">
        <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["website"] ?? ''; ?></span>
    </div>
    <div class="input-label">
        <label for="bio"><?php _se('Bio'); ?></label>
        <textarea name="bio" id="bio" class="resize-vertical" placeholder="<?php _se('Tell us a little bit about you'); ?>" maxlength="255"><?php echo Handler::var('safe_post')["bio"] ?? Handler::var('safe_html_user')["bio"]; ?></textarea>
        <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["bio"] ?? ''; ?></span>
    </div>
</div>
