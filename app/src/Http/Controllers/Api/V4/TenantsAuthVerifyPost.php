<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Http\Controllers\Api\V4;

use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Header;
use Chevere\Http\Status;

#[Response(
    new Status(200),
    new Header('Content-Type', 'application/json'),
)]
class TenantsAuthVerifyPost extends Controller
{
    public function __invoke(): void
    {
    }
}
