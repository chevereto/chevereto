<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\G\Handler;

return function (Handler $handler) {
    if ($handler->isRequestLevel(2)) {
        $handler->issueError(404);

        return;
    }
    $handler::setVar('pre_doctitle', _s('API version %s', '1.1'));
};
