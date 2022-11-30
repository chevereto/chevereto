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
use Chevereto\Encryption\NullEncryption;
use function Chevereto\Legacy\feedback;
use function Chevereto\Legacy\feedbackAlert;

try {
    assertEncryption();
} catch (Throwable $e) {
    feedbackAlert($e->getMessage());
    die(255);
}

$doing = 'Decrypting';
$fromEncryption = encryption();
$toEncryption = new NullEncryption();
feedbackAlert('🔐 Assuming database encrypted');
require __DIR__ . '/cipher.php';

feedback('🔓 Secrets decrypted');
