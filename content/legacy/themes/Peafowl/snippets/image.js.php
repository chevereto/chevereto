<?php

use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\getSetting;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
} ?>
<script data-cfasync="false">
    var divLoading = document.createElement("div");
    var panelThumbs = document.querySelector('.panel-thumbs');
    var panelThumbList = document.querySelectorAll('.panel-thumb-list > li');
	image_viewer_full_fix = function() {
        // var isVideo = <?php echo Handler::var('image')["type"] === 2 ? 'true' : 'false'; ?>;
        // if(isVideo) return;
		// var viewer = document.getElementById("image-viewer"),
        //     zoomAble = viewer.getElementsByTagName('img')[0],
		// 	top = document.getElementById("top-bar"),
		// 	imgSource = {
		// 		width: <?php echo Handler::var('image')["width"]; ?>,
		// 		height: <?php echo Handler::var('image')["height"]; ?>
		// 	},
		// 	img = {width: imgSource.width, height: imgSource.height},
		// 	ratio = imgSource.width/imgSource.height;
		// var canvas = {
		// 		height: window.innerHeight - (typeof top !== "undefined" ? top.clientHeight : 0),
		// 		width: viewer.clientWidth
		// 	};
		// var viewer_banner_top = <?php echo getSetting('banner_image_image-viewer_top') ? 1 : 0; ?>,
		// 	viewer_banner_foot = <?php echo getSetting('banner_image_image-viewer_foot') ? 1 : 0; ?>;
		// var viewer_banner_height = 90;
		// if(viewer_banner_top) {
		// 	canvas.height -= viewer_banner_height + 20;
		// }
		// if(viewer_banner_foot) {
		// 	canvas.height -= viewer_banner_height + 20;
		// }
        // if (panelThumbList.length > 0) {
        //     canvas.height -= panelThumbs.offsetHeight;
        // }
        // canvas.height -= 60;
		// var hasClass = function(element, cls) {
		// 	return (" " + element.className + " ").indexOf(" " + cls + " ") > -1;
		// }
		// if(img.width > canvas.width) {
		// 	img.width = canvas.width;
		// }
        // img.height = (img.width/ratio);
        // if(zoomAble.dataset.is360 == '0') {
        //     if(img.height > canvas.height && (img.height/img.width) < 3) {
        //     	img.height = canvas.height;
        //     }
        //     if(img.height == canvas.height) {
        //     	img.width = (img.height * ratio);
        //     }
        //     if(imgSource.width !== img.width) {
        //         if(img.width > canvas.width) {
        //             img.width = canvas.width;
        //             img.height = (img.width/ratio);
        //         } else if((img.height/img.width) > 3) {
        //             img = imgSource;
        //             if(img.width > canvas.width) {
        //                 img.width = canvas.width * 0.8;
        //             }
        //             img.height = (img.width/ratio);
        //         }
        //     }
        //     if(imgSource.width > img.width || img.width <= canvas.width) {
        //     	if(img.width == canvas.width || imgSource.width == img.width) {
        //     		zoomAble.className = zoomAble.className.replace(/\s+cursor-zoom-(in|out)\s+/, " ");
        //     	} else {
        //     		if(!hasClass(zoomAble, "cursor-zoom-in")) {
        //     			zoomAble.className += " cursor-zoom-in";
        //     		} else {
        //     			zoomAble.className = zoomAble.className.replace(/\s+cursor-zoom-in\s+/, " ");
        //                 if(!hasClass(zoomAble, "cursor-zoom-in")) {
        //                     zoomAble.className += " cursor-zoom-in";
        //                     styleContainer = false;
        //                 }
        //     		}
        //     	}
        //         zoomAble.className = zoomAble.className.trim().replace(/ +/g, ' ');
        //     }
        // }
        //  img = {
        //     width: img.width + "px",
        //     height: img.height + "px",
        //     display: "block"
        // }
        // if(zoomAble.style.width !== img.width) {
        //     for(var k in img) {
        //         zoomAble.style[k] = img[k];
        //     }
        // }
        // if (panelThumbList.length > 0) {
        //     document.querySelector('#panel-thumb-list li.current').scrollIntoView({
        //         behavior: 'auto',
        //         block: 'nearest',
        //         inline: 'nearest'
        //     });
        // }
	}
	// image_viewer_full_fix();
	document.addEventListener('DOMContentLoaded', function(event) {
		CHV.obj.image_viewer.image = {
			width: <?php echo Handler::var('image')["width"]; ?>,
			height: <?php echo Handler::var('image')["height"]; ?>,
			ratio: <?php echo number_format((float) (Handler::var('image')['ratio'] ?? 1), 6, '.', ''); ?>,
			url: "<?php echo Handler::var('image')["url"]; ?>",
			medium: {
				url: "<?php echo Handler::var('image')["medium"]["url"] ?? ''; ?>"
			},
            display_url: "<?php echo Handler::var('image')["display_url"]; ?>",
            url_viewer: "<?php echo Handler::var('image')["url_viewer"]; ?>",
            path_viewer: "<?php echo Handler::var('image')["path_viewer"]; ?>",
            is_360: <?php echo Handler::var('image')["is_360"] ? 'true' : 'false'; ?>,
		};
		CHV.obj.image_viewer.album = {
			id_encoded: "<?php echo Handler::var('image')["album"]["id_encoded"] ?? ''; ?>"
		};
		CHV.fn.image_viewer_full_fix = window["image_viewer_full_fix"];
		// image_viewer_full_fix();
	});
</script>
