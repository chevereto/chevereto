<?php

use Chevereto\Legacy\Classes\Listing;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\filter_string_polyfill;
use function Chevereto\Legacy\G\get_current_url;
use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\show_banner;
use function Chevereto\Vars\get;
use function Chevereto\Vars\server;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
/** @var Listing $listing */
$listing = Handler::hasVar('listing') ? Handler::var('listing') : get_global('listing');
$tabs = (array) (get_global('tabs') ? get_global('tabs') : Handler::var('tabs'));
$isClassic = isset(get()['pagination']) || getSetting('listing_pagination_mode') == 'classic';
$do_pagination = !isset($listing->pagination) or $listing->pagination == true ? true : false;
?>
<?php
foreach ($tabs as $tab) {
    if (isset($tab['list']) && $tab['list'] === false) {
        continue;
    }
    if ($tab['current']) {
        ?>
        <div id="<?php echo $tab["id"]; ?>" class="tabbed-content content-listing visible list-<?php echo $tab["type"]; ?>" data-action="list" data-list="<?php echo $tab["type"]; ?>" data-params="<?php echo $tab["params"]; ?>" data-params-hidden="<?php echo $tab["params_hidden"] ?? ''; ?>">
            <?php
                    if ($listing->output !== []) {
                        ?>
                <div class="pad-content-listing"><?php echo $listing->htmlOutput($listing->outputTpl() ?? null); ?></div>
                <?php
                            if (count($listing->output) >= $listing->limit()) {
                                ?>
                    <div class="content-listing-loading"></div>
                    <?php
                            }
                        if ($do_pagination and ($isClassic or count($listing->output) >= $listing->limit())) { // pagination
                            if ($isClassic) {
                                show_banner('listing_before_pagination', $listing->sfw());
                            }
                            if ($listing->has_page_prev() || $listing->has_page_next()) {
                                ?>
                        <ul class="content-listing-pagination<?php if ($isClassic) {
                                    ?> visible<?php
                                } ?>" data-visibility="<?php echo $isClassic ? 'visible' : 'hidden'; ?>" data-content="listing-pagination" data-type="<?php echo $isClassic ? 'classic' : 'endless'; ?>">
                            <?php
                                $currentUrlPath = add_ending_slash(preg_replace('/\?.*/', '', get_current_url()));
                                $QS = filter_string_polyfill(server()['QUERY_STRING'] ?? '');
                                parse_str($QS, $current_page_qs);
                                unset($current_page_qs['lang'], $current_page_qs['viewer']);
                                $current_url = $currentUrlPath . '?' . http_build_query($current_page_qs);
                                $page = intval((get()['page'] ?? $current_page_qs['page'] ?? null) ?: 1);
                                $pages = [];
                                foreach (['prev', 'next'] as $v) {
                                    $params = $current_page_qs;
                                    $seek = $listing->{'seek' . ($v == 'prev' ? 'Start' : 'End')};
                                    if ($listing->{'has_page_' . $v}()) {
                                        $params['page'] = $v == 'prev' ? ($page - 1) : ($page + 1);
                                        if ($seek) {
                                            unset($params['peek'], $params['seek']);
                                            $params[$v == 'prev' ? 'peek' : 'seek'] = $seek;
                                        }
                                        ${$v . 'Url'} = $currentUrlPath . '?' . http_build_query($params);
                                    } else {
                                        ${$v . 'Url'} = null;
                                    }
                                }
                                $pages['prev'] = [
                                        'label' => '<span class="icon fas fa-angle-left"></span>',
                                        'url' => $prevUrl ?? null,
                                        'disabled' => !$listing->has_page_prev()
                                    ];
                                $pages[] = [
                                        'label' => $page,
                                        'url' => null,
                                        'current' => true
                                    ];
                                $pages['next'] = [
                                        'label' => '<span class="icon fas fa-angle-right"></span>',
                                        'url' => $nextUrl ?? null,
                                        'load-more' => !$isClassic,
                                        'disabled' => !$listing->has_page_next(),
                                    ];
                                foreach ($pages as $k => $page) {
                                    if (is_numeric($k)) {
                                        $li_class = 'pagination-page';
                                    } else {
                                        $li_class = 'pagination-' . $k;
                                    }
                                    if (($page['current'] ?? false) == true) {
                                        $li_class .= ' pagination-current';
                                    }
                                    if (($page['disabled'] ?? false) == true) {
                                        $li_class .= ' pagination-disabled';
                                    } ?><li class="<?php echo $li_class; ?>"><a data-pagination="<?php echo $k; ?>" <?php
                                                                                                                                    if (!is_null($page['url'])) {
                                                                                                                                        ?>href="<?php echo $page['url']; ?>" <?php
                                                                                                                                    } ?>><?php echo $page['label']; ?></a></li><?php
                                } ?>
                        </ul>
                    <?php
                            }
                            if ($isClassic) {
                                show_banner('listing_after_pagination', $listing->sfw());
                            }
                        } // pagination?
                        if ($do_pagination && $isClassic == false) {
                            ?>
                    <div class="content-listing-more">
                        <button class="btn default" data-action="load-more" data-seek="<?php echo $listing->seekEnd; ?>"><i class="fas fa-plus-circle"></i> <?php _se('Load more'); ?></button>
                    </div>
            <?php
                        }
                    } else { // Results?
                        include_theme_file("snippets/template_content_empty");
                    } ?>
        </div>
    <?php
    } else { // !current
            ?>
        <div id="<?php echo $tab["id"]; ?>" class="tabbed-content content-listing hidden list-<?php echo $tab["type"]; ?>" data-action="list" data-list="<?php echo $tab["type"]; ?>" data-params="<?php echo $tab["params"]; ?>" data-params-hidden="<?php echo $tab["params_hidden"] ?? ''; ?>" data-load="<?php echo $isClassic ? 'classic' : 'ajax'; ?>">
        </div>
<?php
    }
} // for
?>
<?php
include_theme_file("snippets/viewer_template");
include_theme_file("snippets/templates_content_listing");
?>
