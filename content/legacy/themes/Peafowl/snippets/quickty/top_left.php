<?php
use Chevereto\Legacy\Classes\Login;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<div id="top-left">
    <div class="top-button pop-btn">
        <div class="top-button-icon fas fa-bars"><span class="btn-text display-none"><?php echo Handler::var('safe_html_website_name'); ?></span></div>
        <div class="pop-box menu-box">
            <?php
            $buttons = Handler::var('pages_link_visible');
            array_unshift($buttons, [
                'icon' => 'fas fa-home',
                'title' => _s('Home'),
                'url' => get_base_url(),
            ]);
            if (Login::isLoggedUser() == false) {
                array_push($buttons, [
                    'icon' => 'fas fa-sign-in-alt',
                    'title' => _s('Sign in'),
                    'url' => get_base_url('login'),
                ]);
                if (getSetting('enable_signups')) {
                    array_push($buttons, [
                        'icon' => 'fas fa-user-plus',
                        'title' => _s('Sign up'),
                        'url' => get_base_url('signup'),
                    ]);
                }
            }
            foreach ($buttons as $k => $button) {
                ?>
                <a role="button" href="<?php echo $button['url']; ?>">
                    <span class="icon <?php echo $button['icon']; ?>"></span>
                    <span class="text"><?php echo $button['title']; ?></span>
                </a>
            <?php
            } ?>
        </div>
    </div>
