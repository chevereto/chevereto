<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\get_public_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\get_checkbox_html;
use function Chevereto\Legacy\get_enabled_languages;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
if (Handler::cond('dashboard_user') or Handler::cond('content_manager')) {
    if (Handler::var('user')['registration_ip']) {
        ?>
    <div data-modal="modal-add-ip_ban" class="hidden" data-submit-fn="CHV.fn.ip_ban.add.submit" data-before-fn="CHV.fn.ip_ban.add.before" data-ajax-deferred="CHV.fn.ip_ban.add.complete">
        <span class="modal-box-title"><i class="fas fa-ban"></i> <?php _se('Add IP ban'); ?></span>
        <div class="modal-form">
            <?php include_theme_file('snippets/form_ip_ban_edit'); ?>
        </div>
    </div>
    <?php
    } ?>
    <ul class="tabbed-content-list table-li margin-bottom-20">
<?php
    foreach (Handler::var('user_list_values') as $v) {
        ?>
        <li><span class="c4 display-table-cell padding-right-10"><?php echo $v['label']; ?></span> <span class="display-table-cell"><?php echo $v['content']; ?></span></li>
    <?php
    } ?>
    </ul>
    <div class="c5 phablet-c1">
        <div class="input-label">
            <label for="status"><?php _se('Status'); ?></label>
            <select name="status" id="status" class="text-input">
                <?php
                        foreach ([
                            'valid' => _s('Valid'),
                            'banned' => _s('Banned'),
                            'awaiting-email' => _s('Awaiting email'),
                            'awaiting-confirmation' => _s('Awaiting confirmation')
                        ] as $k => $v) {
                            $selected = $k == Handler::var('user')["status"] ? " selected" : "";
                            echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>' . "\n";
                        } ?>
            </select>
        </div>
        <?php
                if (Login::isAdmin()) {
                    ?>
            <div class="input-label">
                <label for="role"><?php _se('Role'); ?></label>
                <select name="role" id="role" class="text-input">
                    <?php
                                foreach ([
                                    'user' => ['label' => _s('User'), 'selected' => !Handler::var('user')['is_admin']],
                                    'manager' => ['label' => _s('Manager'), 'selected' => Handler::var('user')['is_manager']],
                                    'admin' => ['label' => _s('Administrator'), 'selected' => Handler::var('user')['is_admin']],
                                ] as $k => $v) {
                                    $selected = $v['selected'] ? " selected" : "";
                                    echo '<option value="' . $k . '"' . $selected . '>' . $v['label'] . '</option>' . "\n";
                                } ?>
                </select>
            </div>
        <?php
                } ?>
    </div>
    <hr class="line-separator">
    </hr>
<?php
} ?>
<div class="c12 phablet-c1">
    <div class="input-label">
        <label for="username"><?php _se('Username'); ?></label>
        <input autocomplete="nickname" type="text" name="username" id="username" maxlength="<?php echo getSetting('username_max_length'); ?>" class="text-input" value="<?php echo Handler::var('safe_post')["username"] ?? Handler::var('user')["username"]; ?>" pattern="<?php echo getSetting('username_pattern'); ?>" rel="tooltip" title='<?php _se('%i to %f characters<br>Letters, numbers and "_"', ['%i' => getSetting('username_min_length'), '%f' => getSetting('username_max_length')]); ?>' data-tipTip="right" placeholder="<?php _se('Username'); ?>" required>
        <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["username"] ?? ''; ?></span>
        <?php
            if (getSetting('website_mode') == 'community') {
                ?>
            <div class="input-below"><?php echo get_public_url(
                    getSetting('root_route') === 'user'
                        ? ''
                        : getSetting('route_user')
                ) . '/'; ?><span data-text="username"><?php echo Handler::var('user')["username"]; ?></span></div>
        <?php
            } ?>
    </div>
    <?php
        if (Handler::cond('owner') || Login::isAdmin()) {
            ?>
        <div class="input-label">
            <label for="email"><?php _se('Email address'); ?></label>
            <input autocomplete="email" type="email" name="email" id="email" class="text-input" value="<?php echo Handler::var('safe_post')["email"] ?? Handler::var('user')["email"]; ?>" placeholder="<?php _se('Your email address'); ?>" <?php if (Handler::cond('email_required')) {
                ?> required<?php
            } ?>>
            <span class="input-warning red-warning"><?php echo Handler::var('input_errors')["email"] ?? ''; ?></span>
            <?php if (Handler::var('changed_email_message')) {
                ?><div class="input-below highlight padding-5 display-inline-block"><?php echo Handler::var('changed_email_message'); ?></div><?php
            } ?>
        </div>
    <?php
        } ?>
</div>
<hr class="line-separator">
</hr>
<?php if (getSetting('enable_expirable_uploads')) { ?>
    <div class="input-label">
        <label for="image_expiration"><?php _se('Auto delete uploads'); ?></label>
        <div class="c6 phablet-1">
            <select type="text" name="image_expiration" id="image_expiration" class="text-input">
                <?php
                        echo get_select_options_html(Image::getAvailableExpirations(), Handler::var('safe_post') ? Handler::var('safe_post')['image_expiration'] : Handler::var('user')['image_expiration']); ?>
            </select>
        </div>
        <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')["image_expiration"] ?? ''; ?></div>
        <div class="input-below"><?php _se('This setting will apply to all your image uploads by default. You can override this setting on each upload.'); ?></div>
    </div>
    <hr class="line-separator">
    </hr>
<?php
        } ?>
<?php if (getSetting('upload_image_exif_user_setting')) {
            ?>
    <div class="input-label">
        <label><?php _se('Image Exif data'); ?></label>
        <?php echo get_checkbox_html([
                    'name' => 'image_keep_exif',
                    'label' => _s('Keep image <a %s>Exif data</a> on upload', 'href="https://www.google.com/search?q=Exif" target="_blank"'),
                    'checked' => ((bool) (Handler::var('safe_post')['image_keep_exif'] ?? Handler::var('user')['image_keep_exif']))
                ]); ?>
    </div>
<?php
        } ?>
<div class="input-label">
    <label><?php _se('Newsletter'); ?></label>
    <?php echo get_checkbox_html([
            'name' => 'newsletter_subscribe',
            'label' => _s('Send me emails with news about %s', Handler::var('safe_html_website_name')),
            'checked' => ((bool) (Handler::var('safe_post')['newsletter_subscribe'] ?? Handler::var('user')['newsletter_subscribe']))
        ]); ?>
</div>
<div class="input-label">
    <label><?php _se('Content settings'); ?></label>
    <?php echo get_checkbox_html([
            'name' => 'show_nsfw_listings',
            'label' => _s('Show not safe content in listings (from others)'),
            'checked' => ((bool) (Handler::var('safe_post')['show_nsfw_listings'] ?? Handler::var('user')['show_nsfw_listings']))
        ]); ?>
</div>
<hr class="line-separator"></hr>
<?php if (getSetting('language_chooser_enable')) {
            ?>
    <div class="c5 phablet-c1">
        <div class="input-label">
            <label for="language"><?php _se('Language'); ?></label>
            <select name="language" id="language" class="text-input">
                <?php
                        $enabled_languages = get_enabled_languages();
            foreach ($enabled_languages as $k => $v) {
                $selected_lang = $k == Handler::var('user')['language'] ? " selected" : "";
                echo '<option value="' . $k . '"' . $selected_lang . '>' . $v["name"] . '</option>' . "\n";
            } ?>
            </select>
        </div>
    </div>
<?php
        } ?>
<?php
    $zones = timezone_identifiers_list();
foreach ($zones as $zone) {
    $zone = explode('/', $zone);
    if (in_array($zone[0], ["Africa", "America", "Antarctica", "Arctic", "Asia", "Atlantic", "Australia", "Europe", "Indian", "Pacific"])) {
        if (isset($zone[1]) != '') {
            $regions[$zone[0]][$zone[0] . '/' . $zone[1]] = str_replace('_', ' ', $zone[1]);
        }
    }
} ?>
<div class="input-label">
    <label for="timezone"><?php _se('Timezone'); ?></label>
    <div class="overflow-auto">
        <select id="timezone-region" class="c5 phablet-c1 grid-columns margin-right-10 phone-margin-bottom-10 phablet-margin-bottom-10 text-input" data-combo="timezone-combo">
            <option><?php _se('Select region'); ?></option>
<?php
$user_region = preg_replace("/(.*)\/.*/", "$1", Handler::var('user')["timezone"]);
foreach ($regions ?? [] as $key => $region) {
    $selected = $user_region == $key ? " selected" : "";
    echo '<option value="' . $key . '"' . $selected . '>' . $key . '</option>';
} ?>
        </select>
        <div id="timezone-combo" class="c5 phablet-c1 grid-columns">
            <?php
                foreach ($regions ?? [] as $key => $region) {
                    $show_hide = $user_region == $key ? "" : " soft-hidden"; ?>
                <select id="timezone-combo-<?php echo $key; ?>" class="text-input switch-combo<?php echo $show_hide; ?>" data-combo-value="<?php echo $key; ?>">
                    <?php
                            foreach ($region as $k => $l) {
                                $selected = Handler::var('user')["timezone"] == $k ? " selected" : "";
                                echo '<option value="' . $k . '"' . $selected . '>' . $l . '</option>' . "\n";
                            } ?>
                </select>
            <?php
                } ?>
        </div>
    </div>
    <input type="hidden" id="timezone" name="timezone" data-content="timezone" data-highlight="#timezone-region" value="<?php echo Handler::var('user')["timezone"]; ?>" required>
</div>
