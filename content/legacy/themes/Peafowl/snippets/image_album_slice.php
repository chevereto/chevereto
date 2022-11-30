<?php

use function Chevereto\Legacy\G\get_global;
use Chevereto\Legacy\G\Handler;

// @phpstan-ignore-next-line
if (!defined('ACCESS') || !ACCESS) {
    die('This file cannot be directly accessed.');
}
$image_album_slice = get_global('image_album_slice') ?: Handler::var('image_album_slice');
$image_id = Handler::hasVar('image') ? Handler::var('image')['id'] : get_global("image_id");
if (is_array($image_album_slice) && is_array($image_album_slice["images"]) && count($image_album_slice["images"]) > 0) {
    foreach ($image_album_slice["images"] as $album_image) {
        ?><li<?php if ($album_image["id"] == $image_id) {
            echo ' class="current"';
        } ?><?php if ($album_image['nsfw']) {
            echo ' data-flag="unsafe"';
        } ?>><a href="<?php echo $album_image["path_viewer"]; ?>"><img class="image-container" src="<?php echo $album_image["thumb"]["url"] ?? ''; ?>" alt="<?php echo $album_image["name"]; ?>"></a></li><?php
    } ?><li class="more-link"><a href="<?php echo $image_album_slice["url"]; ?>" title="<?php _se('view more'); ?>"><span class="fas fa-images"></span></a></li><?php
} ?>
