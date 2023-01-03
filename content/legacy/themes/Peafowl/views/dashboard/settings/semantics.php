<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\G\Handler;

if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
echo read_the_docs_settings('semantics', _s('Semantics')); ?>
<div class="input-label"><i class="fas fa-info-circle"></i> <?php _se('Semantics allows you to define the nouns used for content to customize your content driven experience.'); ?></div>
<?php foreach (Settings::SEMANTICS as $messages) {
    foreach ($messages as $key => $message) {
        $value = Settings::get($key);
        $errors = Handler::var('input_errors')[$key] ?? '';
        $pattern = '^[a-Z0-9]+(?:-[a-Z0-9]+)*$';
        echo <<<STRING
        <div class="input-label">
            <label for="{$key}">{$message}</label>
            <div class="c9 phablet-c1">
                <input type="text" name="{$key}" id="{$key}" class="text-input" value="{$value}" pattern="{$pattern}" placeholder="{$message}">
            </div>
            <div class="input-below input-warning red-warning">{$errors}</div>
        </div>
        STRING;
    }
} ?>
