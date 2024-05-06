<?php

use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<div class="header header-tabs no-select header--centering default-margin-bottom">
    <h1 class="header-title">
        <span class="header-icon fas fa-cog"></span>
        <span class="phone-hide"><?php echo Handler::var('dashboard_menu')[Handler::var('dashboard')]['label']; ?></span>
    </h1>
    <div data-content="pop-selection" class="pop-btn pop-keep-click header-link float-left margin-left-5" data-action="settings-switch">
        <div class="pop-btn-text">
            <span class="icon <?php echo Handler::var('settings')['icon']; ?>"></span>
            <span class="btn-text"><?php echo Handler::var('settings')['label']; ?></span>
            <span class="fas fa-angle-down"></span>
        </div>
        <div class="pop-box pbcols3 anchor-left arrow-box arrow-box-top">
            <div class="pop-box-inner pop-box-menu pop-box-menucols">
                <ul>
                    <?php
                    foreach (Handler::var('settings_menu') as $item) {
                        $class = $item['current'] ? ' current' : '';
                        $class .= $item['isPaid'] ? ' paid' : '';
                        $aHref = $item['url']
                            ? (' href="' . $item['url'] . '"')
                            : '';
                        echo <<<TPL
                            <li class="with-icon$class">
                                <a{$aHref}><span class="btn-icon {$item['icon']}"></span><span class="btn-text">{$item['label']}</span></a>
                            </li>
                        TPL;
                    }
                        ?>
                </ul>
            </div>
        </div>
    </div>
    <?php if (Handler::var('settings')['key'] == 'categories') { ?>
        <div class="header-content-right">
            <div class="header--height header--centering">
                <a class="btn btn-small default" data-modal="form" data-target="modal-add-category"><i class="fas fa-plus"></i><span class="margin-left-5 phone-hide"><?php _se('Create'); ?></span></a>
            </div>
        </div>
        <div data-modal="modal-add-category" class="hidden" data-submit-fn="CHV.fn.category.add.submit" data-before-fn="CHV.fn.category.add.before" data-ajax-deferred="CHV.fn.category.add.complete">
            <span class="modal-box-title"><i class="fas fa-columns"></i> <?php _se('Create %s', _s('category')); ?></span>
            <div class="modal-form">
                <?php include_theme_file('snippets/form_category_edit'); ?>
            </div>
        </div>
    <?php
                        } ?>
    <?php if (Handler::var('settings')['key'] == 'ip-bans') { ?>
        <div class="header-content-right">
            <div class="header--height header--centering">
                <a class="btn btn-small default" data-modal="form" data-target="modal-add-ip_ban"><i class="fas fa-plus"></i><span class="margin-left-5 phone-hide"><?php _se('Add'); ?></span></a>
            </div>
        </div>
        <div data-modal="modal-add-ip_ban" class="hidden" data-submit-fn="CHV.fn.ip_ban.add.submit" data-before-fn="CHV.fn.ip_ban.add.before" data-ajax-deferred="CHV.fn.ip_ban.add.complete">
            <span class="modal-box-title"><i class="fas fa-ban"></i> <?php _se('Add IP ban'); ?></span>
            <div class="modal-form">
                <?php include_theme_file('snippets/form_ip_ban_edit'); ?>
            </div>
        </div>
    <?php
                        } ?>
    <?php if (Handler::var('settings')['key'] == 'external-storage') { ?>
        <div class="header-content-right">
            <div class="header--height header--centering">
                <a class="btn btn-small default" data-modal="form" data-target="modal-add-storage"><i class="fas fa-plus"></i><span class="margin-left-5 phone-hide"><?php _se('Add'); ?></span></a>
            </div>
        </div>
        <div data-modal="modal-add-storage" class="hidden" data-submit-fn="CHV.fn.storage.add.submit" data-before-fn="CHV.fn.storage.add.before" data-ajax-deferred="CHV.fn.storage.add.complete">
            <span class="modal-box-title"><i class="fas fa-hdd"></i> <?php _se('Add storage'); ?></span>
            <div class="modal-form">
                <?php include_theme_file('snippets/form_storage_edit'); ?>
            </div>
        </div>
    <?php
                        } ?>
    <?php if (Handler::var('settings')['key'] == 'pages') {
                            switch (Handler::var('settings_pages')['doing']) {
            case 'add':
            case 'edit':
                $pages_top_link = [
                    'href' => 'dashboard/settings/pages',
                    'text' => '<i class="fas fa-chevron-left margin-right-5"></i>' . _s('Return to pages'),
                ];

                break;
            default:
                $pages_top_link = [
                    'href' => 'dashboard/settings/pages/add',
                    'text' => '<i class="fas fa-plus"></i><span class="margin-left-5 phone-hide">' . _s('Add') . '</span>',
                ];

                break;
        } ?>
        <div class="header-content-right">
            <div class="header--height header--centering">
                <a class="btn btn-small default" href="<?php echo get_base_url($pages_top_link['href']); ?>"><?php echo $pages_top_link['text']; ?></a>
            </div>
        </div>
    <?php
                        } ?>
</div>
