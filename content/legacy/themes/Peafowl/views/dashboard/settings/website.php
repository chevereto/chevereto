<?php

use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\inputDisabledPaid;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('website', _s('Website')); ?>
<div class="input-label c10">
    <label for="website_name"><?php _se('Website name'); ?></label>
    <input type="text" name="website_name" id="website_name" class="text-input" value="<?php echo safe_html(Settings::get('website_name')); ?>" required>
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['website_name'] ?? ''; ?></div>
</div>
<div class="input-label c10">
    <label for="website_doctitle"><?php _se('Website doctitle'); ?></label>
    <input type="text" name="website_doctitle" id="website_doctitle" class="text-input" value="<?php echo safe_html(Settings::get('website_doctitle')); ?>">
</div>
<div class="input-label c10">
    <label for="website_description"><?php _se('Website description'); ?></label>
    <input type="text" name="website_description" id="website_description" class="text-input" value="<?php echo safe_html(Settings::get('website_description')); ?>">
    <div class="input-warning red-warning"><?php echo Handler::var('input_errors')['website_description'] ?? ''; ?></div>
</div>
<hr class="line-separator">
<?php
$zones = timezone_identifiers_list();
foreach ($zones as $tz) {
    $zone = explode('/', $tz);
    $subzone = $zone;
    array_shift($subzone);
    $regions[$zone[0]][$tz] = join('/', $subzone);
} ?>
<div class="input-label">
    <label for="timezone-region"><?php _se('Default time zone'); ?></label>
    <div class="overflow-auto">
        <div class="c5 phablet-c1 phone-c1 grid-columns phone-margin-bottom-10 phablet-margin-bottom-10 margin-right-10">
            <select id="timezone-region" class="text-input" data-combo="timezone-combo">
                <option><?php _se('Select region'); ?></option>
                <?php
                $default_timezone = explode('/', Settings::get('default_timezone') ?? '');
foreach ($regions ?? [] as $key => $region) {
    $selected = $default_timezone[0] == $key ? ' selected' : '';
    echo '<option value="' . $key . '"' . $selected . '>' . $key . '</option>';
} ?>
            </select>
        </div>
        <div id="timezone-combo" class="c5 phablet-c1 grid-columns">
            <?php
            foreach ($regions ?? [] as $key => $region) {
                $show_hide = $default_timezone[0] == $key ? '' : ' soft-hidden';
                if (count($region) == 1) {
                    $show_hide .= ' hidden';
                } ?>
                <select id="timezone-combo-<?php echo $key; ?>" class="text-input switch-combo<?php echo $show_hide; ?>" data-combo-value="<?php echo $key; ?>">
                    <?php
                    foreach ($region as $k => $l) {
                        $selected = Settings::get('default_timezone') == $k ? ' selected' : '';
                        echo '<option value="' . $k . '"' . $selected . '>' . $l . '</option>' . "\n";
                    } ?>
                </select>
            <?php
            } ?>
        </div>
    </div>
    <input type="hidden" id="default_timezone" name="default_timezone" data-content="timezone" data-highlight="#timezone-region" value="<?php echo Settings::get('default_timezone'); ?>" required>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="website_search"><?php _se('Search'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="website_search" id="website_search" class="text-input" data-combo="website-search-combo">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_search')); ?>
        </select></div>
    <div class="input-below"><?php _se('Allows to search images, albums and users based on a given search query.'); ?></div>
</div>
<div id="website-search-combo">
    <div data-combo-value="1" class="switch-combo phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['website_search'] : Settings::get('website_search')) != 1) {
                echo ' soft-hidden';
            } ?>">
        <div class="input-label">
            <label for="website_search_guest"><?php _se('Search'); ?> (<?php _se('guests'); ?>)</label>
            <div class="c5 phablet-c1"><select type="text" name="website_search_guest" id="website_search_guest" class="text-input">
                    <?php
                    echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_search_guest')); ?>
                </select></div>
            <div class="input-below"><?php _se('Enables %s for guests.', _s('search')); ?></div>
        </div>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="website_explore_page"><?php _se('Explore'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="website_explore_page" id="website_explore_page" class="text-input" data-combo="website-explore-combo">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_explore_page')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enables to browse public uploaded files, categories, tags and users.'); ?></div>
</div>
<div id="website-explore-combo">
    <div data-combo-value="1" class="switch-combo phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['website_explore_page'] : Settings::get('website_explore_page')) != 1) {
                echo ' soft-hidden';
            } ?>">
        <div class="input-label">
            <label for="website_explore_page_guest"><?php _se('Explore'); ?> (<?php _se('guests'); ?>)</label>
            <div class="c5 phablet-c1"><select type="text" name="website_explore_page_guest" id="website_explore_page_guest" class="text-input">
                    <?php
                    echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_explore_page_guest')); ?>
                </select></div>
            <div class="input-below"><?php _se('Enables %s for guests.', _s('explore')); ?></div>
        </div>
    </div>
</div>
<hr class="line-separator">
<div class="input-label">
    <label for="website_random"><?php _se('Random'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="website_random" id="website_random" class="text-input" data-combo="website-random-combo">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_random')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enables to browse files randomly.'); ?></div>
</div>
<div id="website-random-combo">
    <div data-combo-value="1" class="switch-combo phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['website_random'] : Settings::get('website_random')) != 1) {
                echo ' soft-hidden';
            } ?>">
        <div class="input-label">
            <label for="website_random_guest"><?php _se('Random'); ?> (<?php _se('guests'); ?>)</label>
            <div class="c5 phablet-c1"><select type="text" name="website_random_guest" id="website_random_guest" class="text-input">
                    <?php
                    echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('website_random_guest')); ?>
                </select></div>
            <div class="input-below"><?php _se('Enables %s for guests.', _s('random')); ?></div>
        </div>
    </div>
</div>
<?php if((bool) (env()['CHEVERETO_ENABLE_POWERED_BY_SETTING'] ?? true)) { ?>
<hr class="line-separator">
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="enable_powered_by"><?php _se('Powered by'); ?> Chevereto</label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="enable_powered_by" id="enable_powered_by" class="text-input">
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_powered_by')); ?>
        </select></div>
    <div class="input-below"><?php _se('Enable this if you want to show a "%s" link at the footer.', _s('Powered by') . ' Chevereto'); ?></div>
</div>
<?php } ?>
<hr class="line-separator">
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="enable_likes"><?php _se('Likes'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="enable_likes" id="enable_likes" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_likes')); ?>
        </select></div>
    <div class="input-below"><?php _se('Allows users to like content and populate "Most liked" listings.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="enable_followers"><?php _se('Followers'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="enable_followers" id="enable_followers" class="text-input" <?php if (getSetting('website_mode') == 'personal') {
                echo ' disabled';
            } ?>>
            <?php
            echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('enable_followers')); ?>
        </select></div>
    <div class="input-below"><?php _se('Followers allows users to follow each other.'); ?></div>
    <?php personal_mode_warning(); ?>
</div>
<hr class="line-separator">
<div class="input-label">
    <?php echo badgePaid('lite'); ?><label for="website_mode"><?php _se('Website mode'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('lite'); ?> type="text" name="website_mode" id="website_mode" class="text-input" data-combo="website-mode-combo">
            <?php
            echo get_select_options_html(['community' => _s('Multi-user'), 'personal' => _s('Single profile')], Settings::get('website_mode')); ?>
        </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['website_mode'] ?? ''; ?></div>
    <div class="input-below"><?php _se('You can switch the website mode anytime.'); ?></div>
</div>
<div id="website-mode-combo">
    <div data-combo-value="personal" class="switch-combo phablet-c1<?php if (
        (Handler::var('safe_post')['website_mode'] ?? Settings::get('website_mode')) != 'personal'
    ) {
                echo ' soft-hidden';
            } ?>">
        <hr class="line-separator">
        <div class="input-label">
            <?php echo badgePaid('lite'); ?><label for="website_mode_personal_uid"><?php _se('%s target %t', ['%s' => _s('Single profile'), '%t' => _n('user', 'users', 1)]); ?></label>
            <div class="c3"><input <?php echo inputDisabledPaid('lite'); ?> type="number" min="1" name="website_mode_personal_uid" id="website_mode_personal_uid" class="text-input" value="<?php echo Settings::get('website_mode_personal_uid'); ?>" placeholder="<?php _se('User ID'); ?>" rel="tooltip" title="<?php _se('Your user id is: %s', Login::getUser()['id']); ?>" data-tipTip="right" data-required></div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['website_mode_personal_uid'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Numeric ID of the target %t for %s mode.', ['%s' => _s('Single profile'), '%t' => _n('user', 'users', 1)]); ?></div>
        </div>
        <div class="input-label">
            <?php echo badgePaid('lite'); ?><label for="website_mode_personal_routing"><?php _se('%s routing', _s('Single profile')); ?></label>
            <div class="c5"><input <?php echo inputDisabledPaid('lite'); ?> type="text" name="website_mode_personal_routing" id="website_mode_personal_routing" class="text-input" value="<?php echo Settings::get('website_mode_personal_routing'); ?>" placeholder="/"></div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['website_mode_personal_routing'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Custom route to map /username to /something. Use "/" to map to homepage.'); ?></div>
        </div>
        <hr class="line-separator">
    </div>
</div>
<div class="input-label">
    <label for="website_privacy_mode"><?php _se('Website privacy mode'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="website_privacy_mode" id="website_privacy_mode" class="text-input" data-combo="website-privacy-mode-combo">
            <?php
            echo get_select_options_html(['public' => _s('Public'), 'private' => _s('Private')], Settings::get('website_privacy_mode')); ?>
        </select></div>
    <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['website_privacy_mode'] ?? ''; ?></div>
    <div class="input-below"><?php _se('Private mode will make the website only available for registered users.'); ?></div>
</div>
<div id="website-privacy-mode-combo">
    <div data-combo-value="private" class="switch-combo phablet-c1<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['website_privacy_mode'] : Settings::get('website_privacy_mode')) != 'private') {
                echo ' soft-hidden';
            } ?>">
        <div class="input-label">
            <label for="website_content_privacy_mode"><?php _se('Content privacy mode'); ?></label>
            <div class="c5 phablet-c1"><select type="text" name="website_content_privacy_mode" id="website_content_privacy_mode" class="text-input">
                    <?php
                    echo get_select_options_html([
                        'default' => _s('Default'),
                        'private' => _s('Force private (self)'),
                        'private_but_link' => _s('Force private (anyone with the link)'),
                    ], Settings::get('website_content_privacy_mode')); ?>
                </select></div>
            <div class="input-below input-warning red-warning"><?php echo Handler::var('input_errors')['website_content_privacy_mode'] ?? ''; ?></div>
            <div class="input-below"><?php _se('Forced privacy modes will override user selected privacy.'); ?></div>
        </div>
    </div>
</div>
