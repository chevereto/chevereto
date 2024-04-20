<?php

use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use function Chevereto\Legacy\G\get_base_url;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\include_theme_file;
use function Chevereto\Legacy\G\include_theme_footer;
use function Chevereto\Legacy\G\include_theme_header;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\show_banner;
use function Chevereto\Legacy\show_theme_inline_code;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
include_theme_header();
$hasPrev = false;
if (Settings::get('homepage_style') == 'split') {
    show_theme_inline_code('snippets/index.js');
    if (Handler::var('list') !== null) {
        $listing = Handler::var('listing');
        $hasPrev = $listing->has_page_prev();
    }
}
if ($hasPrev == false) { ?>
<div id="home-cover">
	<?php include_theme_file('snippets/homepage_cover_slideshow'); ?>
	<div id="home-cover-content" class="c20 phone-c1 phablet-c1 fluid-column center-box padding-left-10 padding-right-10">
		<?php show_banner('home_before_title', (Handler::var('listing') !== null ? Handler::var('listing')->sfw() : true)); ?>
		<h1><?php echo getSetting('homepage_title_html') ?: _s('Upload and share your media'); ?></h1>
        <p class="c20 center-box text-align-center"><?php echo getSetting('homepage_paragraph_html') ?? _s('Drag and drop anywhere you want and start uploading. Get direct links, BBCode and HTML thumbnails.'); ?></p>
		<div class="home-buttons">
			<?php echo Handler::var('homepage_cta'); ?>
		</div>
		<?php show_banner('home_after_cta', (Handler::var('listing') !== null ? Handler::var('listing')->sfw() : true)); ?>
	</div>
</div>
<?php } ?>
<?php show_banner('home_after_cover', (Handler::var('listing') !== null ? Handler::var('listing')->sfw() : true)); ?>
<?php if (Settings::get('homepage_style') == 'split') {
    ?>

<div class="top-sub-bar follow-scroll margin-bottom-5 margin-top-5">
    <div class="content-width">
        <div class="header header-tabs">
            <h2 class="header-title"><strong><?php
                echo isset($home_user)
                    ? User::getStreamName($home_user['name_short'])
                    : ('<span class="' . Handler::var('list')['icon'] . '"></span><span class="phone-hide margin-left-5">' . Handler::var('list')['label']); ?></span></strong></h1>
            <?php include_theme_file("snippets/tabs"); ?>
            <?php
                if (Handler::cond('content_manager')) {
                    include_theme_file("snippets/user_items_editor"); ?>
            <div class="header-content-right">
                <?php include_theme_file("snippets/listing_tools_editor"); ?>
            </div>
            <?php
                } ?>
        </div>
    </div>
</div>

<div class="content-width">
	<div class="<?php echo (!isset($listing) || count($listing->output) == 0)
        ? 'empty'
        : 'filled'; ?>">
		<div id="content-listing-tabs" class="tabbed-listing">
			<div id="tabbed-content-group">
				<?php
                    include_theme_file("snippets/listing"); ?>
			</div>
		</div>
	</div>
	<?php show_banner('home_after_listing', (Handler::var('listing') !== null ? Handler::var('listing')->sfw() : true)); ?>
	<?php
        if (!Handler::var('logged_user') and getSetting('enable_signups')) {
            ?>
	<div id="home-join" class="c20 fluid-column center-box text-align-center">
		<h2><?php _se('Sign up to unlock all the features'); ?></h2>
		<p><?php _se('Manage your content, create private albums, customize your profile and more.'); ?></p>
		<div class="home-buttons"><a href="<?php echo get_base_url('signup'); ?>" class="btn btn-big accent"><?php _se('Create account'); ?></a></div>
	</div>
	<?php
        } ?>
</div>
<?php
} ?>
<?php include_theme_footer(); ?>
