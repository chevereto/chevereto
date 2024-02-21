<?php

use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\Settings;
use function Chevereto\Legacy\G\get_app_version;
use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\get_route_name;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\G\is_prevented_route;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\get_captcha_invisible_html;
use function Chevereto\Legacy\get_translation_table;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\getSettings;
use function Chevereto\Vars\env;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php if (getSetting('website_search')) {
    ?>
	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "WebSite",
			"url": "<?php echo get_public_url(); ?>",
			"potentialAction": {
				"@type": "SearchAction",
				"target": "<?php echo get_public_url('search/images/?q={q}'); ?>",
				"query-input": "required name=q"
			}
		}
	</script>
<?php
}
if (Handler::cond('captcha_needed') && getSetting('captcha_api') == '3') {
    echo get_captcha_invisible_html();
}
?>
<script data-cfasync="false">
	document.getElementById("chevereto-js").addEventListener("load", function() {
		PF.obj.devices = window.devices;
		PF.fn.window_to_device = window.window_to_device;
		PF.obj.config.base_url = "<?php echo get_base_url(); ?>";
		PF.obj.config.json_api = "<?php echo get_base_url('json'); ?>";
		PF.obj.config.listing.items_per_page = "<?php echo getSetting('listing_items_per_page'); ?>";
		PF.obj.config.listing.device_to_columns = <?php echo json_encode(getSetting('listing_device_to_columns')); ?>;
		PF.obj.config.auth_token = "<?php echo Handler::var('auth_token'); ?>";
		PF.obj.l10n = <?php echo json_encode(get_translation_table()); ?>;
		if (typeof CHV == "undefined") {
			CHV = {
				obj: {},
				fn: {},
				str: {}
			};
		}
		CHV.obj.vars = {
			urls: {
				home: PF.obj.config.base_url,
				search: "<?php echo get_base_url("search"); ?>",
				login: "<?php echo get_base_url("login"); ?>",
			}
		};
		PF.obj.vars = $.extend({}, CHV.obj.vars);
		CHV.obj.config = {
			image: {
				max_filesize: "<?php echo getSetting('upload_max_filesize_mb') . ' MB'; ?>",
				right_click: <?php echo json_encode(getSetting('theme_image_right_click')); ?>,
				load_max_filesize: "<?php echo getSetting('image_load_max_filesize_mb') . ' MB'; ?>",
				max_width: <?php echo json_encode(getSetting('upload_max_image_width')); ?>,
				max_height: <?php echo json_encode(getSetting('upload_max_image_height')); ?>,
			},
			upload: {
				url: <?php echo json_encode(getSetting('enable_uploads_url')); ?>,
				redirect_single_upload: <?php echo json_encode(getSetting('enable_redirect_single_upload')); ?>,
				threads: <?php echo json_encode(getSetting('upload_threads')); ?>,
                image_types: <?php echo json_encode(Image::getEnabledImageFormats()); ?>,
                moderation: <?php echo json_encode(Handler::cond('moderate_uploads')); ?>,
				maxQueue: <?php echo json_encode(Handler::var('upload_max_queue')); ?>,
			},
			user: {
				avatar_max_filesize: "<?php echo getSetting('user_image_avatar_max_filesize_mb') . ' MB'; ?>",
				background_max_filesize: "<?php echo getSetting('user_image_background_max_filesize_mb') . ' MB'; ?>",
			},
			captcha: {
				isNeeded: <?php echo json_encode(Handler::cond('captcha_needed')); ?>,
				version: '<?php echo getSetting('captcha_api'); ?>',
				enabled: <?php echo getSettings()['captcha'] ? 'true' : 'false'; ?>,
				sitekey: "<?php echo getSetting('captcha_sitekey'); ?>",
			},
			listing: {
				viewer: <?php echo Settings::get('listing_viewer') ? 'true' : 'false'; ?>,
			},
            palettesId: <?php echo json_encode(Handler::var('palettes')->handlesToId()); ?>
		};
		<?php
        $page_info = [
            'doctitle' => Handler::var('safe_html_doctitle') ?? '',
            'pre_doctitle' => Handler::var('safe_html_pre_doctitle') ?? ''
        ];
        if ($page_info['pre_doctitle']) {
            $page_info['pos_doctitle'] = str_replace_first($page_info['pre_doctitle'], '', $page_info['doctitle']);
        }
        ?>
		CHV.obj.page_info = <?php echo json_encode($page_info); ?>;
		<?php
        if (Login::isLoggedUser()) {
            $logged_user = Login::getUser();
            $logged_user_array = [];
            foreach (['name', 'username', 'id', 'url', 'url_albums'] as $arr) {
                $logged_user_array[$arr] = $logged_user[$arr == 'id' ? 'id_encoded' : $arr];
            } ?>
			CHV.obj.logged_user = <?php echo json_encode($logged_user_array); ?>;
		<?php
            if (Login::isAdmin()) { ?>
                CHV.obj.system_info = <?php echo json_encode([
                    'version' => get_app_version(),
                    'edition' => env()['CHEVERETO_EDITION'],
                ]); ?>;
        <?php
            }
        }
        if (!is_prevented_route() && !Handler::cond('404') && in_array(get_route_name(), ["image", "album", "user", "settings"]) or (Handler::hasCond('dashboard_user') && Handler::cond('dashboard_user'))) {
            if (in_array(get_route_name(), ["settings", "dashboard"])) {
                $route = ['id' => null, 'url' => null];
                $route_user = Handler::var('user');
            } else {
                $route_var = Handler::var(get_route_name());
                if ($route_var !== null) {
                    $route = $route_var;
                    $route_user = get_route_name() == "user" ? $route : ($route["user"] ?? null);
                }
            } ?>
			CHV.obj.resource = {
                privacy: "<?php echo Handler::var('privacy') ?? ''; ?>",
				id: "<?php echo $route["id_encoded"] ?? ''; ?>",
				type: "<?php echo get_route_name(); ?>",
				url: "<?php echo get_route_name() === "image"
                    ? $route["path_viewer"] ?? ''
                    : $route["url"] ?? ''; ?>",
				parent_url: "<?php echo Handler::var('image') !== null
                    ? (
                        (Handler::var('image')['user']['is_private'] ?? false)
                        ? get_base_url()
                        : Handler::var('image')['album']['url'] ?? ''
                    )
                    : (get_route_name() == 'dashboard'
                        ? null
                        : $route_user['url'] ?? '') ?>"
			};
			<?php
                if (isset($route_user)) { ?>
				CHV.obj.resource.user = {
					name: "<?php echo safe_html($route_user["name"] ?? ''); ?>",
					username: "<?php echo safe_html($route_user["username"] ?? ''); ?>",
					id: "<?php echo $route_user["id_encoded"] ?? 0; ?>",
					url: "<?php echo $route_user["url"] ?? ''; ?>",
					url_albums: "<?php echo $route_user["url_albums"] ?? ''; ?>"
				};
		<?php
                }
        }
        ?>
	});
</script>
