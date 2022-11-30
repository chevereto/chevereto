<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Controllers;

use Chevereto\Controllers\LegacyController;

/**
 * @var String $route `name.php` file.
 */
function legacyController(string $route)
{
    return new LegacyController(dispatch: $route);
}
