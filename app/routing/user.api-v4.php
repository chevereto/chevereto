<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use function Chevere\Router\route;
use function Chevere\Router\routes;
use Chevereto\Controllers\Api\V4\Album\AlbumDeleteController;
use Chevereto\Controllers\Api\V4\Album\AlbumGetController;
use Chevereto\Controllers\Api\V4\Album\AlbumPatchController;
use Chevereto\Controllers\Api\V4\Album\AlbumPostController;
use Chevereto\Controllers\Api\V4\Album\Like\AlbumLikeDeleteController;
use Chevereto\Controllers\Api\V4\Album\Like\AlbumLikePostController;
use Chevereto\Controllers\Api\V4\Image\Bulk\ImageBulkPatchController;
use Chevereto\Controllers\Api\V4\Image\ImageGetController;
use Chevereto\Controllers\Api\V4\Image\ImagePatchController;
use Chevereto\Controllers\Api\V4\Image\ImagePostController;
use Chevereto\Controllers\Api\V4\Image\Like\ImageLikeDeleteController;
use Chevereto\Controllers\Api\V4\Image\Like\ImageLikePostController;
use Chevereto\Controllers\Api\V4\User\Asset\Avatar\UserAssetAvatarDeleteController;
use Chevereto\Controllers\Api\V4\User\Asset\Avatar\UserAssetAvatarPostController;
use Chevereto\Controllers\Api\V4\User\Asset\Background\UserAssetBackgroundDeleteController;
use Chevereto\Controllers\Api\V4\User\Asset\Background\UserAssetBackgroundPostController;
use Chevereto\Controllers\Api\V4\User\Follow\UserFollowDeleteController;
use Chevereto\Controllers\Api\V4\User\Follow\UserFollowPostController;
use Chevereto\Controllers\Api\V4\User\Setting\UserSettingPatchController;

$prefix = '/api/4/user/';

return routes(
    route(
        path: $prefix . 'account/notifications/social/',
        // GET: ,
    ),
    route(
        path: $prefix . 'account/notifications/social/{id}/',
        // PATCH: ,
    ),
    route(
        path: $prefix . 'account/settings/',
        PATCH: new UserSettingPatchController(),
    ),
    route(
        path: $prefix . 'account/login/{service}/',
        // DELETE: ,
    ),
    route(
        path: $prefix . 'albums/',
        POST: new AlbumPostController(),
    ),
    route(
        path: $prefix . 'albums/{id}/',
        DELETE: new AlbumDeleteController(),
        GET: new AlbumGetController(),
        PATCH: new AlbumPatchController(),
    ),
    route(
        path: $prefix . 'albums/{id}/contents/',
        // GET: ,
    ),
    route(
        path: $prefix . 'albums/{id}/like/',
        DELETE: new AlbumLikeDeleteController(),
        POST: new AlbumLikePostController(),
    ),
    route(
        path: $prefix . 'albums/bulk/',
        // DELETE: ,
    ),
    route(
        path: $prefix . 'albums/bulk/parent/',
        // PATCH: ,
    ),
    route(
        path: $prefix . 'albums/list/',
        // GET:
    ),
    route(
        path: $prefix . 'images/',
        POST: new ImagePostController(),
    ),
    route(
        path: $prefix . 'images/{id}/',
        // DELETE: ,
        GET: new ImageGetController(),
        PATCH: new ImagePatchController(),
    ),
    route(
        path: $prefix . 'images/{id}/like/',
        DELETE: new ImageLikeDeleteController(),
        POST: new ImageLikePostController(),
    ),
    route(
        path: $prefix . 'images/bulk/',
        PATCH: new ImageBulkPatchController(),
    ),
    route(
        path: $prefix . 'images/list/',
        // GET:
    ),
    route(
        path: $prefix . 'user/{username}/assets/avatar/',
        DELETE: new UserAssetAvatarDeleteController(),
        POST: new UserAssetAvatarPostController()
    ),
    route(
        path: $prefix . 'user/{username}/assets/background/',
        DELETE: new UserAssetBackgroundDeleteController(),
        POST: new UserAssetBackgroundPostController()
    ),
    route(
        path: $prefix . 'users/{username}/follow/',
        DELETE: new UserFollowDeleteController(),
        POST: new UserFollowPostController(),
    ),
    route(
        path: $prefix . 'users/list/',
        // GET:,
    ),
);
