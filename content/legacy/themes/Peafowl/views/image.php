<?php

use function Chevereto\Legacy\arr_printer;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;
use function Chevereto\Legacy\getFriendlyExif;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\isShowEmbedContent;
use function Chevereto\Legacy\show_banner;
use function Chevereto\Legacy\show_theme_inline_code;
use function Chevereto\Legacy\time_elapsed_string;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
?>
<?php include_theme_header(); ?>
<div id="image-viewer" class="image-viewer full-viewer<?php echo isset(Handler::var('image')['album'], Handler::var('image_album_slice')['images']) ? ' --thumbs' : '';?>">
    <?php
    if (Handler::var('image')['is_approved']) {
        show_banner('image_image-viewer_top', !Handler::var('image')['nsfw']);
    }
    ?>
    <?php
    $image_url = Handler::var('image')['medium']['url']
        ?? Handler::var('image')['frame']['url']
        ?? Handler::var('image')['url'];
    ?>
        <img draggable="false" data-media="<?php echo  Handler::var('image')['type']; ?>" class="media" src="<?php echo $image_url; ?>" <?php if (!getSetting('theme_download_button')) {
        ?> class="no-select" <?php
    } ?> alt="<?php echo Handler::var('image')['alt']; ?>" width="<?php echo Handler::var('image')['width']; ?>" height="<?php echo Handler::var('image')['height']; ?>" data-is360="<?php echo Handler::var('image')['is_360']; ?>" <?php if (isset(Handler::var('image')['medium']) || isset(Handler::var('image')['frame'])) {
        ?> data-load="full"<?php
    } ?>>
        <?php if (Handler::var('image')['is_use_loader']) {
        ?>
        <div id="image-viewer-loader" data-size="<?php echo Handler::var('image')['size']; ?>"><?php if (Handler::var('image')['is_animated'] || Handler::var('image')['type'] === 'video') {
            ?><span class="btn-icon icon fas fa-play-circle"></span><?php
        } ?><span><?php
            switch (true) {
                case Handler::var('image')['is_animated']:
                    _se('Play %s', 'GIF');

                break;
                case Handler::var('image')['type'] === 'video':
                    _se('Play %s', 'video');

                break;
                case Handler::var('image')['is_360']:
                    _se('Load 360° view');

                break;
                default:
                    echo '<i class="fas fa-cloud-download-alt"></i> ' . Handler::var('image')['size_formatted'];

                break;
            } ?></span></div>
    <?php
    } if (Handler::var('image')['is_360']) { ?>
    <div id="image-viewer-360" class="soft-hidden"></div>
    <?php } ?>
    <?php
    if (Handler::var('image')['is_approved']) {
        show_banner('image_image-viewer_foot', !Handler::var('image')['nsfw']);
    }
    ?>
</div>
<?php
show_banner('image_after_image-viewer', !Handler::var('image')['nsfw']);
?>
<?php
if (isset(Handler::var('image')['album'], Handler::var('image_album_slice')['images'])) {
    ?>
<div class="panel-thumbs">
    <div class="content-width">
        <ul id="panel-thumb-list" class="panel-thumb-list" data-content="album-slice"><?php include_theme_file('snippets/image_album_slice'); ?></ul>
        <div class="image-viewer-navigation arrow-navigator">
            <?php
            if (isset(Handler::var('image_album_slice')['prev'])) {
                ?>
                <a class="left-0" data-action="prev" title="◄" href="<?php echo Handler::var('image_album_slice')['prev']['path_viewer']; ?>" title="<?php _se('%s image', _s('Previous')); ?>"><span class="fas fa-angle-left"></span></a>
            <?php
            }
    if (isset(Handler::var('image_album_slice')['next'])) {
        ?>
                <a class="right-0" data-action="next" title="►" href="<?php echo Handler::var('image_album_slice')['next']['path_viewer']; ?>" title="<?php _se('%s image', _s('Next')); ?>"><span class="fas fa-angle-right"></span></a>
            <?php
    } ?>
        </div>
    </div>
</div>
<?php
}
?>
<?php show_theme_inline_code('snippets/image.js'); ?>
<div class="content-width margin-top-10">
    <div class="header header-content margin-bottom-10 margin-top-10">
        <div class="header-content-left">
            <div class="header-content-breadcrum">
            <?php if (isset(Handler::var('image')['user']['id'])) {
    include_theme_file('snippets/breadcrum_owner_card');
} else { ?>
                <div class="breadcrum-item">
                    <div class="user-image default-user-image"><span class="icon fas fa-user-circle"></span></div>
                </div>
<?php } ?>
                <div class="breadcrum-item" data-contains="cta-album">
                    <?php echo Handler::var('image')['album']['cta_html'] ?? ''; ?>
                </div>
            </div>
        </div>
        <div class="header-content-right breaks-ui">
        <?php
                    if (Handler::cond('owner') || Handler::cond('content_manager')) {
                        ?>
                    <a data-action="edit" title="<?php _se('Edit'); ?> (E)" class="btn btn-small default" data-modal="edit"><span class="icon fas fa-edit"></span></a>
                <?php
                if (!Handler::var('image')['is_approved'] && Handler::cond('content_manager')) { ?>
                    <a class="btn btn-small default" data-confirm="<?php _se("Do you really want to approve this image? The image will go public if you approve it."); ?>" data-submit-fn="CHV.fn.submit_resource_approve" data-ajax-deferred="CHV.fn.complete_resource_approve" data-ajax-url="<?php echo get_base_url('json'); ?>"><span class="icon fas fa-check-double"></span><span class="phone-hide margin-left-5"><?php _se('Approve'); ?></span></a>
                <?php
                }
                        if (Handler::cond('allowed_to_delete_content')) {
                            ?>
                    <a data-action="delete" title="<?php _se('Delete'); ?> (Del)" class="btn btn-small default" data-confirm="<?php _se("Do you really want to delete this %s?", _n('file', 'files', 1)); ?> <?php _se("This can't be undone."); ?>" data-submit-fn="CHV.fn.submit_resource_delete" data-ajax-deferred="CHV.fn.complete_resource_delete" data-ajax-url="<?php echo get_base_url('json'); ?>"><span class="icon fas fa-trash-alt"></span></a>
            <?php
                        }
                    }
            ?>
            <?php if (getSetting('theme_download_button')) {
                ?>
                <a data-action="download" href="<?php echo Handler::var('image')['url']; ?>" download="<?php echo Handler::var('image')['filename']; ?>" class="btn btn-small default btn-download" title="<?php _se('Download'); ?>"><span class="btn-icon fas fa-download"></span></a>
            <?php
            } ?>
            <?php if (isset(Handler::var('image')['album']['id']) && (Handler::cond('owner') || Handler::cond('content_manager'))) {
                ?>
                <a class="btn-album-cover" data-album-id="<?php echo Handler::var('image')['album']['id_encoded']; ?>" data-id="<?php echo Handler::var('image')['id_encoded']; ?>" data-cover="<?php echo (int) Handler::cond('album_cover'); ?>" title="<?php _se('Cover'); ?> (H)">
                    <span data-action="album-cover" class="btn btn-small default btn-album-is-cover" rel="tooltip" title="<?php _se('This is the album cover'); ?>"><span class="btn-icon fas fa-check-square"></span></span>
                    <span data-action="album-cover" class="btn btn-small default btn-album-not-cover"><span class="btn-icon far fa-square"></span></span>
                </a>
            <?php
            } ?>
            <?php if (getSetting('theme_show_social_share')) {
                ?>
                <a class="btn btn-small default" data-action="share" title="<?php _se('Share'); ?> (S)"><span class="btn-icon fas fa-share-alt"></span></a>
            <?php
            } ?>
            <?php if (getSetting('enable_likes')) {
                ?>
                <a class="btn-like" title="<?php _se('Like'); ?> (L)" data-type="image" data-id="<?php echo Handler::var('image')['id_encoded']; ?>" data-liked="<?php echo (int) (Handler::var('image')['liked'] ?? false); ?>">
                    <span data-action="like" class="btn btn-small default btn-liked" rel="tooltip" title="<?php _se('You like this'); ?>"><span class="btn-icon fas fa-heart"></span><span class="btn-text" data-text="likes-count"><?php echo (int) (Handler::var('image')['likes'] ?? false); ?></span></span>
                    <span data-action="like" class="btn btn-small default btn-unliked"><span class="btn-icon far fa-heart"></span><span class="btn-text" data-text="likes-count"><?php echo (int) (Handler::var('image')['likes'] ?? false); ?></span></span>
                </a>
            <?php
            }
            ?>
        </div>
    </div>
    <?php
    if (Handler::var('image')['is_approved']) {
        show_banner('image_before_header', !Handler::var('image')['nsfw']);
    }
    ?>
    <div class="header margin-bottom-10">
    <?php
    if (!Handler::var('image')['title']) {
        ?>
        <h1 class="header-title phone-float-none viewer-title soft-hidden">
            <a data-text="image-title" href="<?php echo Handler::var('image')['path_viewer']; ?>"><?php echo Handler::var('pre_doctitle'); ?></a>
        </h1>
    <?php
    } else { ?>
        <h1 class="header-title phone-float-none viewer-title">
            <a data-text="image-title" href="<?php echo Handler::var('image')['path_viewer']; ?>"><?php echo nl2br(Handler::var('image_safe_html')['title'] ?? ''); ?></a>
        </h1>
    <?php } ?>
    </div>
    <p class="description-meta margin-bottom-10">
        <span class="icon far fa-eye-slash <?php if (!isset(Handler::var('image')['album']) or Handler::var('image')['album']['privacy'] == 'public') {
        echo 'soft-hidden';
    } ?>" data-content="privacy-private" title="<?php _se('This content is private'); ?>" rel="tooltip"></span>
        <?php
         echo sprintf('<span class="fas fa-%s"></span>', Handler::var('image')['type'])
            . ' ' . Handler::var('image')['width'] . ' × ' . Handler::var('image')['height']
            . (
                Handler::var('image')['type'] === 'video'
                ? (' — <i class="far fa-clock"></i> ' . Handler::var('image')['duration_time'])
                : ''
            )
            . ' — ' . strtoupper(Handler::var('image')['extension'])
            . ' ' . Handler::var('image')['size_formatted']; ?>
    </p>
    <p class="description-meta margin-bottom-20">
        <?php
        if (isset(Handler::var('image')['category_id'])) {
            $category = Handler::var('categories')[Handler::var('image')['category_id']] ?? null;
        }
        if (isset($category)) {
            $category_link = '<a href="' . $category['url'] . '" rel="tag"><i class="fas fa-columns margin-right-5"></i>' . $category['name'] . '</a>';
        }
        $time_elapsed_string = '<span title="' . Handler::var('image')['date_fixed_peer'] . '">' . time_elapsed_string(Handler::var('image')['date_gmt']) . '</span>';
        if (isset(Handler::var('image')['album']['id']) && (Handler::var('image')['album']['privacy'] !== 'private_but_link' || Handler::cond('owner') || Handler::cond('content_manager'))) {
            $album_link = '<a href="' . Handler::var('image')['album']['url'] . '"' . (Handler::var('image')['album']['name'] !== Handler::var('image')['album']['name_truncated'] ? (' title="' . Handler::var('image')['album']['name_html'] . '"') : null) . '><i class="fas fa-images margin-right-5"></i>' . Handler::var('image')['album']['name_truncated_html'] . '</a>';
            if (isset($category_link)) {
                echo _s('Added to %a under %s %t', ['%a' => $album_link, '%s' => $category_link, '%t' => _s('category')]);
            } else {
                echo _s('Added to %s', $album_link);
            }
            echo ' — ' . $time_elapsed_string;
        } else {
            if (isset($category_link)) {
                echo _s('Uploaded to %s', $category_link) . ' — ' . $time_elapsed_string;
            } else {
                _se('Uploaded %s', $time_elapsed_string);
            }
        }
        echo ' — ' . Handler::var('image')['views'] . ' ' . Handler::var('image')['views_label'];
    if (Handler::var('image')['expiration_date_gmt'] ?? false) { ?>
    <span class="user-select-none" rel="tooltip" data-tipTip="top" title="<?php _se('This content will be removed on %s', Handler::var('image')['expiration_date_gmt'] . ' UTC'); ?>" data-text="image-expiration"><i class="fas fa-bomb"></i> <?php echo _s('Expires'); ?></span>
    <?php
    } ?>
    </p>
    <div class="header margin-bottom-10 no-select">
        <?php include_theme_file('snippets/tabs'); ?>
    </div>
    <?php
    if (Handler::var('image')['is_approved']) {
        show_banner('image_after_header', !Handler::var('image')['nsfw']);
    }
    ?>
    <div id="tabbed-content-group">
        <div id="tab-about" class="tabbed-content<?php echo Handler::var('current_tab') == 'about' ? ' visible' : ''; ?>">
            <div class="c16 phone-c1 phablet-c1 grid-columns margin-right-10">
                <div class="panel-description default-margin-bottom">
                    <p class="description-text margin-bottom-5" data-text="image-description"><?php echo nl2br(Handler::var('image_safe_html')['description'] ?? _s('No description provided.')); ?></p>
                    <?php
                    if (getSetting('theme_show_exif_data')) {
                        $image_exif = getFriendlyExif(Handler::var('image')['original_exifdata']);
                        if ($image_exif) {
                            ?>
                            <p class="exif-meta margin-top-20">
                                <span class="camera-icon fas fa-camera"></span><?php echo $image_exif->Simple->Camera; ?>
                                <span class="exif-data"><?php echo $image_exif->Simple->Capture; ?> — <a class="font-size-small" data-toggle="exif-data" data-html-on="<?php _se('Less Exif data'); ?>" data-html-off="<?php _se('More Exif data'); ?>"><?php _se('More Exif data'); ?></a></span>
                            </p>
                            <div data-content="exif-data" class="soft-hidden">
                                <ul class="tabbed-content-list table-li">
                                    <?php
                                    foreach ($image_exif->Full as $k => $v) {
                                        $label = preg_replace('/(?<=\\w)(?=[A-Z])/', ' $1', $k);
                                        if (ctype_upper(preg_replace('/\s+/', '', $label))) {
                                            $label = $k;
                                        } ?>
                                        <li><span class="c5 display-table-cell padding-right-10"><?php echo $label; ?></span> <span class="display-table-cell"><?php echo $v; ?></span></li>
                                    <?php
                                    } ?>
                                </ul>
                            </div>
                    <?php
                        } // $image_exif
                    } // theme_show_exif_data
                    ?>
                </div>
                <?php
                if (Handler::cond('content_manager')) {
                    ?>
                    <div class="tabbed-content-section">
                        <ul class="tabbed-content-list table-li">
                            <?php
                            $image_admin_list_values = Handler::var('image_admin_list_values');
                    if (isset(Handler::var('image')['album']['id'])) {
                        $album_values = [
                                    'label' => _s('%s ID', _n('Album', 'Albums', 1)),
                                    'content' => Handler::var('image')['album']['id'] . ' (' . Handler::var('image')['album']['id_encoded'] . ')',
                                ];
                        $image_admin_list_values = array_slice($image_admin_list_values, 0, 1, true) +
                                    [
                                        'album' => [
                                            'label' => _s('%s ID', _n('Album', 'Albums', 1)),
                                            'content' => Handler::var('image')['album']['id'] . ' (' . Handler::var('image')['album']['id_encoded'] . ')',
                                        ],
                                    ] +
                                    array_slice($image_admin_list_values, 1, count($image_admin_list_values) - 1, true);
                    }
                    foreach ($image_admin_list_values as $v) {
                        ?>
                                <li><span class="c5 display-table-cell padding-right-10 phone-display-block font-weight-bold"><?php echo $v['label']; ?></span><span class="display-table-cell phone-display-block word-break-break-all"><?php echo $v['content']; ?></span></li>
                            <?php
                    } ?>
                        </ul>
                        <div data-modal="modal-add-ip_ban" class="hidden" data-submit-fn="CHV.fn.ip_ban.add.submit" data-before-fn="CHV.fn.ip_ban.add.before" data-ajax-deferred="CHV.fn.ip_ban.add.complete">
                            <span class="modal-box-title"><i class="fas fa-ban"></i> <?php _se('Add IP ban'); ?></span>
                            <div class="modal-form">
                                <?php include_theme_file('snippets/form_ip_ban_edit'); ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="c8 phablet-c1 fluid-column grid-columns margin-left-10 phablet-margin-left-0">
                <?php
                if (Handler::var('image')['is_approved']) {
                    show_banner('content_tab-about_column', !Handler::var('image')['nsfw']);
                }
                ?>
            </div>
        </div>
        <div id="tab-comments" class="tabbed-content<?php echo Handler::var('current_tab') == 'comments' ? ' visible' : ''; ?>">
            <?php
            if (Handler::var('image')['is_approved']) {
                show_banner('content_before_comments', !Handler::var('image')['nsfw']);
            }
            ?>
            <div class="comments c16 phone-c1 phablet-c1 grid-columns margin-right-10">
                <?php echo Handler::var('comments'); ?>
            </div>
        </div>
        <?php if (isShowEmbedContent()) {
                ?>
            <div id="tab-embeds" class="tabbed-content<?php echo Handler::var('current_tab') == 'embeds' ? ' visible' : ''; ?>">
                <div class="c24 margin-left-auto margin-right-auto">
                    <div class="margin-bottom-30 growl static text-align-center clear-both" data-content="privacy-private"><?php echo Handler::var('image')['album']['privacy_notes'] ?? ''; ?></div>
                </div>
                <div class="panel-share c24 phone-c1 phablet-c1 grid-columns margin-right-10">
                    <?php
                    foreach (Handler::var('embed') as $embed) {
                        ?>
                        <div class="panel-share-item">
                            <h4 class="pre-title"><?php echo $embed['label']; ?></h4>
                            <?php foreach ($embed['entries'] as $entry) {
                            ?>
                                <div class="panel-share-input-label">
                                    <div class="title c5 grid-columns"><?php echo $entry['label']; ?></div>
                                    <div class="c19 phablet-c1 grid-columns">
                                        <input id="<?php echo $entry['id']; ?>" type="text" class="text-input" value="<?php echo $entry['value']; ?>" data-focus="select-all" readonly>
                                        <button type="button" class="input-action" data-action="copy" data-action-target="#<?php echo $entry['id']; ?>"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
                                    </div>
                                </div>
                            <?php
                        } ?>
                        </div>
                    <?php
                    } ?>
                </div>
            </div>
        <?php
            } ?>
        <?php
        if (Handler::cond('admin')) {
            ?>
            <div id="tab-info" class="tabbed-content<?php echo Handler::var('current_tab') == 'info' ? ' visible' : ''; ?>">
                <?php echo arr_printer(Handler::var('image_safe_html'), '<li><div class="c4 display-table-cell padding-right-10 font-weight-bold">%K</div> <div class="display-table-cell">%V</div></li>', ['<ul class="tabbed-content-list table-li">', '</ul>']); ?>
            </div>
        <?php
        }
        ?>
    </div>
    <?php
    if (Handler::var('image')['is_approved']) {
        show_banner('image_footer', !Handler::var('image')['nsfw']);
    }
    ?>
</div>
<?php
if (Handler::cond('owner') || Handler::cond('content_manager')) {
        ?>
    <div data-modal="form-modal" class="hidden" data-submit-fn="CHV.fn.submit_image_edit" data-before-fn="CHV.fn.before_image_edit" data-ajax-deferred="CHV.fn.complete_image_edit" data-ajax-url="<?php echo get_base_url('json'); ?>">
        <span class="modal-box-title"><i class="fas fa-edit"></i> <?php _se('Edit %s', _n('image', 'images', 1)); ?></span>
        <div class="modal-form">
            <?php
            include_theme_file('snippets/form_image'); ?>
        </div>
    </div>
<?php
    }
include_theme_footer(); ?>
