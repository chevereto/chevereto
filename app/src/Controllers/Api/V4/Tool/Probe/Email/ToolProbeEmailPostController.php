<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Tool\Probe\Email;

use Chevereto\Controllers\WorkflowController;

class ToolProbeEmailPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Probe email delivery.';
    }

    public function run(string $email): array
    {
        return [];
    }
}
