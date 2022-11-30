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

use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetimegmt;

class Confirmation
{
    public static function get(
        array|string $values,
        array $sort = [],
        int $limit = 1
    ): array|bool {
        return DB::get('confirmations', $values, 'AND', $sort, $limit);
    }
    
    public static function insert(array $values): int
    {
        if (!isset($values['status'])) {
            $values['status'] = 'active';
        }
        $values['date'] = datetime();
        $values['date_gmt'] = datetimegmt();
        
        return DB::insert('confirmations', $values);
    }
    
    public static function update($id, $values): bool
    {
        return DB::update('confirmations', $values, ['id' => $id]) > 0;
    }
    
    public static function delete($values, $clause = 'AND'): int
    {
        return DB::delete('confirmations', $values, $clause);
    }
}
