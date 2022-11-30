<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers;

use Chevere\Controller\Controller;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Response\Interfaces\ResponseInterface;

final class LegacyController extends Controller
{
    final public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        return $this
            ->getResponse(
                document: $arguments->getString('document'),
            );
    }
}
