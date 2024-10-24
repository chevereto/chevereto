<?php

use Chevereto\Legacy\G\Handler;

use function Chevereto\Legacy\G\get_base_url;
use function Chevereto\Legacy\G\require_theme_footer;
use function Chevereto\Legacy\G\require_theme_header;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<?php require_theme_header(); ?>
<div class="center-box c24 margin-top-20 padding-bottom-10">
	<div class="content-width">
		<div class="header default-margin-bottom">
			<h1 class="header-title"><i class="fa-solid fa-project-diagram color-accent margin-right-5"></i>API version 1.1</h1>
		</div>
		<div class="text-content">

			<p>Our API enables programmatic file uploads, allowing you to seamlessly integrate our uploading functionality into your own applications.</p>

			<h2><i class="fa-solid fa-key color-accent margin-right-5"></i><?php _se('Key'); ?></h2>
<?php if(getSetting('guest_uploads') && (getSetting('api_v1_key') ?? '') !== '') { ?>
			<div class="input-label">
				<label><?php _se('Public API key'); ?></label>
				<div class="position-relative">
					<code id="api_guest" data-focus="select-all" class="code code--snippet"><?php echo getSetting('api_v1_key'); ?></code>
					<button type="button" class="input-action" data-action="copy" data-action-target="#api_guest"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
				</div>
			</div>
<?php } ?>
			<p><i class="fa-solid fa-circle-info margin-right-5"></i><?php _se('Get your own API key under your %s.', '<a href="' . get_base_url('settings/api') . '">' . _s('settings') . '</a>'); ?></p>

      <h2><i class="fa-solid fa-bolt color-accent margin-right-5"></i><?php _se('Request %s', _s('method')); ?></h2>
			<p>API V1 calls can be made using POST or GET request methods.</p>
			<p>POST request method is <strong>recommended</strong>.</p>

			<h2><i class="fa-solid fa-server color-accent margin-right-5"></i><?php _se('Request %s', 'URL'); ?></h2>
			<div class="input-label">
				<code id="api_endpoint" data-focus="select-all" class="code code--snippet"><?php echo get_base_url('api/1/upload', true); ?></code>
				<button type="button" class="input-action" data-action="copy" data-action-target="#api_endpoint"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
			</div>

			<h2 id="auth"><i class="fa-solid fa-user-shield color-accent margin-right-5"></i><?php _se('Authorization'); ?></h2>
			<p>API V1.1 supports header authorization by passing the <code class="code code--inline-background">X-API-Key</code> header with an API key.</p>
			<code class="code code--snippet">X-API-Key: chv_key_here</code>

			<h2><i class="fa-solid fa-gear color-accent margin-right-5"></i><?php _se('Parameters'); ?></h2>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>source</h3>
			<p>A binary file, base64 data, or a URL for an image.</p>

      <h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>key <span class="optional">(optional)</span></h3>
			<p>The API key. You can use this parameter if unable to provide auth via headers.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>title <span class="optional">(optional)</span></h3>
			<p>File title. This is automatically detected from metadata if not provided.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>description <span class="optional">(optional)</span></h3>
			<p>File description. This is automatically detected from metadata if not provided.</p>

      <h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>tags <span class="optional">(optional)</span></h3>
			<p>File tag(s). Comma separated list of tags.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>album_id <span class="optional">(optional)</span></h3>
			<p>File album id, must be owned by the API key user.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>category_id <span class="optional">(optional)</span></h3>
			<p>Category id. Determines the file category to assign.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>width <span class="optional">(optional)</span></h3>
			<p>Target resize width, will automatic detect height.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>expiration <span class="optional">(optional)</span></h3>
			<p>Expiration time to auto-delete the file in date interval format. For example, PT5M for five minutes in the future. P3D for three days in the future.</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>nsfw <span class="optional">(optional)</span></h3>
			<p>Not safe for work flag [0, 1].</p>

			<h3><i class="fa-solid fa-caret-right color-accent margin-right-5"></i>format <span class="optional">(optional)</span></h3>
			<p>Return format [json, redirect, txt].</p>

			<h2><i class="fa-solid fa-laptop-code color-accent margin-right-5"></i><?php _se('Example call'); ?></h2>
			<?php
				$api = get_base_url('api/1/upload', true);
				$key = 'YOUR_API_KEY';
				$code = <<<COMMAND
				curl --fail-with-body -X POST \
					-H "X-API-Key: $key" \
					-H "Content-Type: multipart/form-data" \
					-F "source=@image.jpeg" \
					$api
				COMMAND;
			?>
			<div class="input-label">
				<code id="api_call" class="code code--command code--snippet" data-click="select-all"><?php echo $code; ?></code>
				<button type="button" class="input-action" data-action="copy" data-action-target="#api_call"><i class="far fa-copy"></i> <?php _se('copy'); ?></button>
			</div>

			<h2><i class="fa-solid fa-reply color-accent margin-right-5"></i><?php _se('%s response', 'API'); ?></h2>
			<p>API V1 responses will vary depending on the format parameter (json, txt, redirect). When using JSON (default) the response output will contain the <code class="code code--inline-background">status_txt</code> and <code class="code code--inline-background">status_code</code> properties.</p>

			<h2><?php _se('Example response'); ?> (JSON)</h2>
<pre><code class="code code--snippet">{
  "status_code": 200,
  "success": {
    "message": "file uploaded",
    "code": 200
  },
  "image": {
    "name": "Badgers-animated-music-video",
    "extension": "mp4",
    "size": 3011299,
    "width": 496,
    "height": 360,
    "date": "2024-10-10 16:58:00",
    "date_gmt": "2024-10-10 19:58:00",
    "title": "Badgers animated music video MrWeebl",
    "tags": [],
    "description": null,
    "nsfw": 0,
    "storage_mode": "datefolder",
    "md5": "7a120d5c28de264bdbb934f023a628fd",
    "source_md5": null,
    "original_filename": "Badgers _ animated music video _ MrWeebl.mp4",
    "original_exifdata": null,
    "views": 0,
    "category_id": null,
    "chain": 21,
    "thumb_size": 21212,
    "medium_size": 0,
    "frame_size": 19804,
    "expiration_date_gmt": "2024-10-10 20:28:00",
    "likes": 0,
    "is_animated": 0,
    "is_approved": 1,
    "is_360": 0,
    "duration": 73,
    "type": "video",
    "tags_string": "",
    "file": {
      "resource": {
        "type": "url"
      }
    },
    "id_encoded": "ZfGd",
    "filename": "Badgers-animated-music-video.mp4",
    "mime": "video/mp4",
    "url": "http://localhost/images/2024/10/10/Badgers-animated-music-video.mp4",
    "ratio": 1.3777777777777778,
    "size_formatted": "3 MB",
    "frame": {
      "filename": "Badgers-animated-music-video.fr.jpeg",
      "name": "Badgers-animated-music-video.fr",
      "mime": "image/jpeg",
      "extension": "jpeg",
      "url": "http://localhost/images/2024/10/10/Badgers-animated-music-video.fr.jpeg",
      "size": 19804
    },
    "image": {
      "filename": "Badgers-animated-music-video.mp4",
      "name": "Badgers-animated-music-video",
      "mime": "video/mp4",
      "extension": "mp4",
      "url": "http://localhost/images/2024/10/10/Badgers-animated-music-video.mp4",
      "size": 3011299
    },
    "thumb": {
      "filename": "Badgers-animated-music-video.th.jpeg",
      "name": "Badgers-animated-music-video.th",
      "mime": "image/jpeg",
      "extension": "jpeg",
      "url": "http://localhost/images/2024/10/10/Badgers-animated-music-video.th.jpeg",
      "size": 21212
    },
    "url_frame": "http://localhost/images/2024/10/10/Badgers-animated-music-video.fr.jpeg",
    "medium": {
      "filename": null,
      "name": null,
      "mime": null,
      "extension": null,
      "url": null
    },
    "duration_time": "01:13",
    "url_viewer": "http://localhost/clip/Badgers-animated-music-video-MrWeebl.ZfGd",
    "path_viewer": "/clip/Badgers-animated-music-video-MrWeebl.ZfGd",
    "url_short": "http://localhost/clip/ZfGd",
    "display_url": "http://localhost/images/2024/10/10/Badgers-animated-music-video.fr.jpeg",
    "display_width": 496,
    "display_height": 360,
    "views_label": "views",
    "likes_label": "likes",
    "how_long_ago": "moments ago",
    "date_fixed_peer": "2024-10-10 19:58:00",
    "title_truncated": "Badgers animated music vi...",
    "title_truncated_html": "Badgers animated music vi...",
    "is_use_loader": false,
    "display_title": "Badgers animated music video MrWeebl",
    "delete_url": "http://localhost/clip/ZfGd/delete/e8b07479818bc58d3b9849c431e9c2b28827ccce7809ed4f"
  },
  "status_txt": "OK"
}</code></pre>
			<h2><?php _se('Example response'); ?> (text)</h2>
			<code class="code code--snippet" data-click="select-all">http://localhost/images/2024/10/10/Badgers-animated-music-video.mp4</code>

			<h2><?php _se('Example response'); ?> (redirect)</h2>
			<code class="code code--snippet" data-click="select-all">Location: /clip/Badgers-animated-music-video-MrWeebl.ZfGd</code>
		</div>
	</div>
</div>
<?php require_theme_footer(); ?>
