<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Encryption\assertEncryption;
use function Chevereto\Encryption\encryption;
use Chevereto\Encryption\Encryption;
use Chevereto\Encryption\Key;
use Chevereto\Encryption\NullEncryption;
use function Chevereto\Legacy\feedback;
use function Chevereto\Legacy\feedbackAlert;

try {
    assertEncryption();
} catch (Throwable $e) {
    feedbackAlert($e->getMessage());
    die(255);
}
$opts = getopt('C:k:') ?: [];
$key = $opts['k'] ?? '';
$doing = 'Encrypting';
$fromEncryption = $key === ''
    ? new NullEncryption()
    : new Encryption(new Key($opts['k']));
$toEncryption = encryption();
feedbackAlert(
    $key === ''
        ? '🔓 Assuming no database encryption'
        : '🔑 Using provided key for decrypting database'
);
require __DIR__ . '/cipher.php';

feedback('🔐 Secrets encrypted');
