<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Http\Controllers\Api\V4\TenantDelete;
use Chevereto\Http\Controllers\Api\V4\TenantGet;
use Chevereto\Http\Controllers\Api\V4\TenantInstallPost;
use Chevereto\Http\Controllers\Api\V4\TenantPatch;
use Chevereto\Http\Controllers\Api\V4\TenantPlanDelete;
use Chevereto\Http\Controllers\Api\V4\TenantPlanGet;
use Chevereto\Http\Controllers\Api\V4\TenantPlanPatch;
use Chevereto\Http\Controllers\Api\V4\TenantsAuthVerifyPost;
use Chevereto\Http\Controllers\Api\V4\TenantsGet;
use Chevereto\Http\Controllers\Api\V4\TenantsPlansGet;
use Chevereto\Http\Controllers\Api\V4\TenantsPlansPost;
use Chevereto\Http\Controllers\Api\V4\TenantsPost;
use Chevereto\Http\Controllers\Api\V4\TenantUserPasswordResetPatch;
use Chevereto\Http\Middlewares\RestrictIpAccess;
use Chevereto\Http\Middlewares\SignedRequest;
use Chevereto\Http\Middlewares\TenantsApiKeyAuthorization;
use function Chevere\Router\route;
use function Chevere\Router\routes;
use function Chevereto\Vars\env;

return routes(
    route(
        '/_/api/4/auth/verify',
        POST: TenantsAuthVerifyPost::class,
    ),
    route(
        '/_/api/4/tenants',
        POST: TenantsPost::class,
        GET: TenantsGet::class,
    ),
    route(
        '/_/api/4/tenants/{id}',
        GET: TenantGet::class,
        PATCH: TenantPatch::class,
        DELETE: TenantDelete::class,
    ),
    route(
        '/_/api/4/tenants/{id}/install',
        POST: TenantInstallPost::class,
    ),
    route(
        '/_/api/4/tenants/{id}/user-password-reset',
        PATCH: TenantUserPasswordResetPatch::class,
    ),
    route(
        '/_/api/4/tenants-plans',
        POST: TenantsPlansPost::class,
        GET: TenantsPlansGet::class,
    ),
    route(
        '/_/api/4/tenants-plans/{id}',
        GET: TenantPlanGet::class,
        PATCH: TenantPlanPatch::class,
        DELETE: TenantPlanDelete::class,
    ),
)
    ->withAppendMiddleware(
        RestrictIpAccess::with(
            allowList: env()['CHEVERETO_TENANTS_API_IP_ALLOW_LIST']
        ),
        TenantsApiKeyAuthorization::class,
        SignedRequest::with(
            secret: env()['CHEVERETO_TENANTS_API_REQUEST_SECRET'],
        ),
    );
