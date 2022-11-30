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
use Chevereto\Controllers\Api\V4\Ban\Ip\BanIpDeleteController;
use Chevereto\Controllers\Api\V4\Ban\Ip\BanIpPatchController;
use Chevereto\Controllers\Api\V4\Ban\Ip\BanIpPostController;
use Chevereto\Controllers\Api\V4\Category\CategoryPostController;
use Chevereto\Controllers\Api\V4\Image\Bulk\ImageBulkPatchController;
use Chevereto\Controllers\Api\V4\Stat\Rebuild\StatRebuildPostController;
use Chevereto\Controllers\Api\V4\Storage\Migrate\StorageMigratePostController;
use Chevereto\Controllers\Api\V4\Storage\Stat\Regen\StorageStatRegenPostController;
use Chevereto\Controllers\Api\V4\Storage\StoragePostController;
use Chevereto\Controllers\Api\V4\Tool\Id\Decode\ToolDecodeIdGetController;
use Chevereto\Controllers\Api\V4\Tool\Id\Encode\ToolEncodeIdGetController;
use Chevereto\Controllers\Api\V4\Tool\Probe\Email\ToolProbeEmailPostController;
use Chevereto\Controllers\Api\V4\User\Export\UserExportGetController;
use Chevereto\Controllers\Api\V4\User\UserGetController;
use Chevereto\Controllers\Api\V4\User\UserPostController;

$prefix = '/api/4/admin/';

return routes(
    route(
        path: $prefix . 'bans/ip/',
        POST: new BanIpPostController(),
    ),
    route(
        path: $prefix . 'bans/ip/{ip}/',
        DELETE: new BanIpDeleteController(),
        PATCH: new BanIpPatchController(),
    ),
    route(
        path: $prefix . 'categories/',
        POST: new CategoryPostController(),
    ),
    route(
        path: $prefix . 'categories/{id}/',
        // DELETE: ,
        // PATCH: ,
    ),
    route(
        path: $prefix . 'images/bulk/approve/',
        PATCH: new ImageBulkPatchController(),
    ),
    route(
        path: $prefix . 'imports/',
        // POST: ,
    ),
    route(
        path: $prefix . 'imports/{id}/',
        // DELETE: ,
        // GET: ,
        // PATCH: ,
    ),
    route(
        path: $prefix . 'imports/{id}/process/',
        // POST: ,
    ),
    route(
        path: $prefix . 'imports/{id}/reset/',
        // POST: ,
    ),
    route(
        path: $prefix . 'imports/{id}/resume/',
        // POST: ,
    ),
    route(
        path: $prefix . 'stats/rebuild/',
        POST: new StatRebuildPostController(),
    ),
    route(
        path: $prefix . 'storages/',
        POST: new StoragePostController(),
    ),
    route(
        path: $prefix . 'storages/{id}/',
        // PATCH: ,
    ),
    route(
        path: $prefix . 'storages/{id}/migrate/',
        POST: new StorageMigratePostController(),
    ),
    route(
        path: $prefix . 'storages/{id}/stats/regen/',
        POST: new StorageStatRegenPostController(),
    ),
    route(
        path: $prefix . 'tools/id/{id}/decode/',
        GET: new ToolDecodeIdGetController(),
    ),
    route(
        path: $prefix . 'tools/id/{id}/encode/',
        GET: new ToolEncodeIdGetController(),
    ),
    route(
        path: $prefix . 'tools/probe/email/',
        POST: new ToolProbeEmailPostController(),
    ),
    route(
        path: $prefix . 'users/',
        POST: new UserPostController(),
    ),
    route(
        path: $prefix . 'users/{id}/',
        GET: new UserGetController(),
    ),
    route(
        path: $prefix . 'users/{id}/export/',
        GET: new UserExportGetController(),
    ),
);
