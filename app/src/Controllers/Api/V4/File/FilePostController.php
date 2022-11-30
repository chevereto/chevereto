<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\File;

use Chevere\Controller\Controller;
use function Chevere\DataStructure\data;

abstract class FilePostController extends Controller
{
    public function run(
        string $source
    ): array {
        return data();
    }
}
