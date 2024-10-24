<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Encryption;

use Chevereto\Encryption\Interfaces\EncryptionInterface;
use LogicException;

final class EncryptionInstance
{
    private static ?EncryptionInterface $instance;

    public function __construct(EncryptionInterface $encryption)
    {
        self::$instance = $encryption;
    }

    public static function get(): EncryptionInterface
    {
        if (! isset(self::$instance)) {
            throw new LogicException('No Encryption instance present');
        }

        return self::$instance;
    }
}
