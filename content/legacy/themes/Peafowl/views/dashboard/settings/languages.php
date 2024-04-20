<?php

use function Chevereto\Legacy\badgePaid;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\get_available_languages;
use function Chevereto\Legacy\get_enabled_languages;
use function Chevereto\Legacy\get_select_options_html;
use function Chevereto\Legacy\inputDisabledPaid;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('languages', _s('Languages')); ?>
<div class="input-label">
    <label for="default_language"><?php _se('Default language'); ?></label>
    <div class="c5 phablet-c1"><select type="text" name="default_language" id="default_language" class="text-input">
        <?php
                foreach (get_available_languages() as $k => $v) {
                    $selected_lang = $k == Settings::get('default_language') ? ' selected' : '';
                    echo '<option value="' . $k . '"' . $selected_lang . '>' . $v['name'] . '</option>' . "\n";
                } ?>
    </select></div>
    <div class="input-below"><?php _se('Default base language to use.'); ?></div>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="auto_language"><?php _se('Auto language'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="auto_language" id="auto_language" class="text-input">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('auto_language')); ?>
    </select></div>
    <div class="input-below"><?php _se('Enable this if you want to automatically detect and set the right language for each user.'); ?></div>
</div>
<div class="input-label">
    <?php echo badgePaid('pro'); ?><label for="language_chooser_enable"><?php _se('Language chooser'); ?></label>
    <div class="c5 phablet-c1"><select <?php echo inputDisabledPaid('pro'); ?> type="text" name="language_chooser_enable" id="language_chooser_enable" class="text-input" data-combo="language-enable-combo">
        <?php
                echo get_select_options_html([1 => _s('Enabled'), 0 => _s('Disabled')], Settings::get('language_chooser_enable')); ?>
    </select></div>
    <div class="input-below"><?php _se('Enable this if you want to allow language selection.'); ?></div>
</div>
<?php if (count(get_available_languages()) > 0 && (bool) env()['CHEVERETO_ENABLE_LANGUAGE_CHOOSER']) {
                    ?>
<div id="language-enable-combo">
    <div data-combo-value="1" class="switch-combo<?php if ((Handler::var('safe_post') ? Handler::var('safe_post')['language_chooser_enable'] == 0 : !Settings::get('language_chooser_enable'))) {
                        echo ' soft-hidden';
                    } ?>">
        <div class="checkbox-label">
            <h4 class="input-label-label"><?php _se('Enabled languages'); ?></h4>
            <ul class="c20 phablet-c1">
                <?php
                    foreach (get_available_languages() as $k => $v) {
                        $lang_flag = array_key_exists($k, get_enabled_languages()) ? ' checked' : null;
                        echo '<li class="c5 phone-c1 display-inline-block"><label class="display-block" for="languages_enable[' . $k . ']"> <input type="checkbox" name="languages_enable[]" id="languages_enable[' . $k . ']" value="' . $k . '"' . $lang_flag . '>' . $v['name'] . '</label></li>';
                    } ?>
            </ul>
            <p class="margin-top-20"><i class="fas fa-check-square"></i> <?php _se("Only checked languages will be used in your website."); ?></p>
        </div>
    </div>
</div>
<?php
                } ?>
