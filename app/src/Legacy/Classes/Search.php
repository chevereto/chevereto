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

use function Chevereto\Legacy\G\str_replace_first;
use Exception;

class Search
{
    public array $display;

    public static array $excluded = ['storage', 'ip'];

    public string $DBEngine;

    public string $wheres;

    public ?string $q;

    public string $type;

    public array $request;

    public array $requester;

    public array $binds;

    public array $op;

    public function __construct()
    {
        $this->DBEngine = DB::queryFetchSingle("SHOW TABLE STATUS WHERE Name = '" . DB::getTable('images') . "';")['Engine'];
    }

    public function build(): void
    {
        if (!in_array($this->type, ['images', 'albums', 'users'])) {
            throw new Exception('Invalid search type', 600);
        }
        $as_handle = ['as_q' => null, 'as_epq' => null, 'as_oq' => null, 'as_eq' => null, 'as_cat' => 'category'];
        $as_handle_admin = ['as_stor' => 'storage', 'as_ip' => 'ip'];
        if ($this->requester['is_content_manager'] ?? false) {
            $as_handle = array_merge($as_handle, $as_handle_admin);
        }
        foreach ($as_handle as $k => $v) {
            if (isset($this->request[$k]) && $this->request[$k] !== '') {
                $this->q .= ' ' . (isset($v) ? $v . ':' : '') . $this->request[$k];
            }
        }
        $this->q = trim(preg_replace(['#\"+#', '#\'+#'], ['"', '\''], $this->q ?? ''));
        $search_op = $this->handleSearchOperators($this->q, $this->requester['is_content_manager'] ?? false);
        $this->q = null;
        foreach ($search_op as $operator) {
            $this->q .= implode(' ', $operator) . ' ';
        }
        if (isset($this->q)) {
            $this->q = preg_replace('/\s+/', ' ', trim($this->q));
        }
        $q_match = $this->q;
        $search_binds = [];
        $search_op_wheres = [];
        foreach ($search_op['named'] as $v) {
            $q_match = trim(preg_replace('/\s+/', ' ', str_replace($v, '', $q_match)));
            if ($q_match === '') {
                $q_match = null;
            }
            $op = explode(':', $v);
            if (!in_array($op[0], ['category', 'ip', 'storage'])) {
                continue;
            }
            switch ($this->type) {
                case 'images':
                    switch ($op[0]) {
                        case 'category':
                            $search_op_wheres[] = 'category_url_key = :category';
                            $search_binds[] = ['param' => ':category', 'value' => $op[1]];

                        break;

                        case 'ip':
                            $search_op_wheres[] = 'image_uploader_ip LIKE REPLACE(:ip, "*", "%")';
                            $search_binds[] = ['param' => ':ip', 'value' => str_replace_first('ip:', '', $this->q)];

                        break;

                        case 'storage':
                            if (!filter_var($op[1], FILTER_VALIDATE_INT) && !in_array($op[1], ['local', 'external'])) {
                                break;
                            }
                            $storage_operator_clause = [
                                $op[1] => '= :storage_id',
                                'local' => 'IS NULL',
                                'external' => 'IS NOT NULL',
                            ];

                            if (filter_var($op[1], FILTER_VALIDATE_INT)) {
                                $search_binds[] = ['param' => ':storage_id', 'value' => $op[1]];
                            }

                            $search_op_wheres[] = 'image_storage_id ' . ($storage_operator_clause[$op[1]]);

                        break;
                    }

                break;
                case 'albums':
                case 'users':
                    if ($op[0] === 'ip') {
                        $search_binds[] = ['param' => ':ip', 'value' => str_replace_first('ip:', '', $this->q)];
                    }

                break;
            }
        }
        if (isset($q_match)) {
            $q_value = $q_match;
            if ($this->DBEngine == 'InnoDB') {
                $q_value = trim($q_value, '><');
            }
            $search_binds[] = ['param' => ':q', 'value' => $q_value];
        }
        $this->binds = $search_binds;
        $this->op = $search_op;
        $wheres = null;
        switch ($this->type) {
            case 'images':
                if (isset($q_match)) {
                    $wheres = 'WHERE MATCH(`image_name`,`image_title`,`image_description`,`image_original_filename`) AGAINST(:q IN BOOLEAN MODE)';
                }
                if ($search_op_wheres !== []) {
                    $wheres .= (is_null($wheres) ? 'WHERE ' : ' AND ') . implode(' AND ', $search_op_wheres);
                }

            break;
            case 'albums':
                if (empty($search_binds)) {
                    $wheres = 'WHERE album_id < 0';
                } else {
                    $wheres = (($op[0] ?? null) == 'ip' ? 'album_creation_ip LIKE REPLACE(:ip, "*", "%")' : 'WHERE MATCH(`album_name`,`album_description`) AGAINST(:q)');
                }

            break;
            case 'users':
                if (empty($search_binds)) {
                    $wheres = 'WHERE user_id < 0';
                } elseif (($op[0] ?? null) == 'ip') {
                    $wheres = 'user_registration_ip LIKE REPLACE(:ip, "*", "%")';
                } else {
                    $clauses = [
                        'name_username' => 'WHERE MATCH(`user_name`,`user_username`) AGAINST(:q)',
                        'email' => '`user_email` LIKE CONCAT("%", :q, "%")',
                    ];
                    if ($this->requester['is_content_manager'] ?? false) {
                        $pos = strpos($this->q, '@');
                        if ($pos !== false) {
                            if (preg_match_all('/\s+/', $this->q)) {
                                $wheres = $clauses['name_username']
                                    . ' OR ' . $clauses['email'];
                            } else {
                                $wheres = $clauses['email'];
                            }
                        } else {
                            $wheres = $clauses['name_username'];
                        }
                    } else {
                        $wheres = $clauses['name_username'];
                    }
                }

            break;
        }
        $this->wheres = $wheres;
        $this->display = [
            'type' => $this->type,
            'q' => $this->q,
            'd' => strlen($this->q) >= 25 ? (substr($this->q, 0, 22) . '...') : $this->q,
        ];
    }

    protected function handleSearchOperators(string $q, bool $full = true): array
    {
        $operators = ['any' => [], 'exact_phrases' => [], 'excluded' => [], 'named' => []];
        $raw_regex = [
            'named' => '[\S]+\:[\S]+', // take all the like:this operators
            'quoted' => '-*[\"\']+.+[\"\']+', // take all the "quoted stuff" "like" "this, one"
            'spaced' => '\S+', // Take all the space separated stuff
        ];
        foreach ($raw_regex as $k => $v) {
            if ($k == 'spaced') {
                $q = str_replace(',', '', $q);
            }
            if (preg_match_all('/' . $v . '/', $q, $match)) {
                foreach ($match[0] as $qMatch) {
                    switch ($k) {
                        case 'named':
                            if (!$full) {
                                $named_operator = explode(':', $qMatch);
                                if (in_array($named_operator[0], self::$excluded)) {
                                    continue 2;
                                }
                            }
                            $operators[$k][] = $qMatch;

                        break;
                        default:
                            if (0 === strpos($qMatch, '-')) {
                                $operators['excluded'][] = $qMatch;
                            } elseif (0 === strpos($qMatch, '"')) {
                                $operators['exact_phrases'][] = $qMatch;
                            } else {
                                $operators['any'][] = $qMatch;
                            }

                        break;
                    }
                    $q = trim(preg_replace('/\s+/', ' ', str_replace($qMatch, '', $q)));
                }
            }
        }

        return $operators;
    }
}
