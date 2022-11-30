<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use function Chevereto\Legacy\G\datetime_add;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\datetimegmt;

class Lock
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function create(): bool
    {
        $lock = DB::get('locks', ['name' => $this->name], 'AND', [], 1);
        if ($lock !== false) {
            $diff = datetime_diff($lock['lock_expires_gmt']);
            if ($diff < 0) {
                return false;
            }
            $this->destroy();
        }
        $datetime = datetimegmt();
        $insert = DB::insert('locks', [
            'name' => $this->name,
            'date_gmt' => $datetime,
            'expires_gmt' => datetime_add($datetime, 'PT15S'),
        ]);

        return $insert !== false;
    }

    public function destroy(): bool
    {
        DB::delete('locks', ['name' => $this->name]);

        return true;
    }
}
