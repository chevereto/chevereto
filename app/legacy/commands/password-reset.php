<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevere\String\randomString;
use Chevereto\Legacy\Classes\Login;
use Chevereto\Legacy\Classes\User;

$opts = getopt('C:u:') ?: [];
$missing = [];
if (!isset($opts['u'])) {
    echo "[Error] Missing username" . "\n";
    die(255);
}
$password = randomString(24);
$user = User::getSingle($opts['u'], 'username');
if ($user === []) {
    echo "[Error] User not found" . "\n";
    die(255);
}
$changed = Login::changePassword(
    userId: $user['id'],
    password: $password,
    update_session: false
);
if (!$changed) {
    // echo "[NOTICE] User doesn't have a password" . "\n";
    $added = Login::addPassword(
        userId: $user['id'],
        password: $password,
        update_session: false
    );
    if (!$added) {
        echo "[Error] Failed to add password" . "\n";
        die(255);
    }
}
echo $password . "\n";
die(0);
