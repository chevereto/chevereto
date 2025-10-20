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

use Exception;
use function Chevereto\Legacy\G\str_replace_first;

class Search
{
    public const OPERATORS = [
        'q',
        'as_q',
        'as_epq',
        'as_oq',
        'as_eq',
        'as_cat',
        'as_stor',
        'as_ip',
    ];

    public array $display;

    public static array $excluded = ['storage', 'ip'];

    public string $DBEngine = 'InnoDB';

    public string $wheres;

    public string $q = '';

    public string $type;

    public array $request;

    public array $requester;

    public array $binds;

    public array $op;

    public function build(): void
    {
        if (! in_array($this->type, ['images', 'albums', 'users'], true)) {
            throw new Exception('Invalid search type', 600);
        }
        $as_handle = [
            'as_q' => null,
            'as_epq' => null,
            'as_oq' => null,
            'as_eq' => null,
            'as_cat' => 'category',
        ];
        $as_handle_admin = [
            'as_stor' => 'storage',
            'as_ip' => 'ip',
        ];
        if ($this->requester['is_content_manager'] ?? false) {
            $as_handle = array_merge($as_handle, $as_handle_admin);
        }
        $this->q = str_replace('@', '', $this->q);
        foreach ($as_handle as $k => $v) {
            if (isset($this->request[$k]) && $this->request[$k] !== '') {
                if ($k === 'as_epq') {
                    $this->q .= ' "'
                        . $this->request[$k]
                        . '"';
                } else {
                    $this->q .= ' '
                        . (isset($v) ? ($v . ':') : '')
                        . $this->request[$k];
                }
            }
        }
        $this->q = trim(
            preg_replace(
                ['#\"+#', '#\'+#'],
                ['"', '\''],
                $this->q ?? '' // @phpstan-ignore-line
            )
        );
        $exact_phrase = null;
        if (($this->request['as_epq'] ?? '') !== '') {
            $exact_phrase = $this->request['as_epq'];
            if (preg_match('/^"(.*?)"$/', $exact_phrase, $matches)) {
                $exact_phrase = $matches[1];
            }
        }
        $search_op = $this->handleSearchOperators($this->q, $this->requester['is_content_manager'] ?? false);
        $this->q = '';
        foreach ($search_op as $operator) {
            $this->q .= implode(' ', $operator) . ' ';
        }
        if ($this->q !== '') {
            $this->q = trim($this->q);
            $this->q = preg_replace(
                '/\s+/',
                ' ',
                trim($this->q)
            ) ?? '';
        }
        $this->q ??= ''; // @phpstan-ignore-line
        $q_match = $this->q;
        $search_binds = [];
        $search_op_wheres = [];
        foreach ($search_op['named'] as $v) {
            $q_match = trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    str_replace($v, '', $q_match)
                )
            );
            $op = explode(':', $v);
            if (! in_array($op[0], ['category', 'ip', 'storage'], true)) {
                continue;
            }
            switch ($this->type) {
                case 'images':
                    switch ($op[0]) {
                        case 'category':
                            $search_op_wheres[] = 'category_url_key = :category';
                            $search_binds[] = [
                                'param' => ':category',
                                'value' => $op[1],
                            ];

                            break;

                        case 'ip':
                            $search_op_wheres[] = 'image_uploader_ip LIKE REPLACE(:ip, "*", "%")';
                            $search_binds[] = [
                                'param' => ':ip',
                                'value' => str_replace_first('ip:', '', $this->q),
                            ];

                            break;

                        case 'storage':
                            if (! filter_var($op[1], FILTER_VALIDATE_INT)
                                && ! in_array($op[1], ['local', 'external'], true)
                            ) {
                                break;
                            }
                            $storage_operator_clause = [
                                $op[1] => '= :storage_id',
                                'local' => 'IS NULL',
                                'external' => 'IS NOT NULL',
                            ];

                            if (filter_var($op[1], FILTER_VALIDATE_INT)) {
                                $search_binds[] = [
                                    'param' => ':storage_id',
                                    'value' => $op[1],
                                ];
                            }

                            $search_op_wheres[] = 'image_storage_id ' . ($storage_operator_clause[$op[1]]);

                            break;
                    }

                    break;
                case 'albums':
                case 'users':
                    if ($op[0] === 'ip') {
                        $search_op_wheres[] = match ($this->type) {
                            'albums' => <<<SQL
                            album_creation_ip LIKE REPLACE(:ip, "*", "%")
                            SQL,
                            'users' => <<<SQL
                            user_registration_ip LIKE REPLACE(:ip, "*", "%")
                            SQL
                        };
                        $search_binds[] = [
                            'param' => ':ip',
                            'value' => str_replace_first('ip:', '', $this->q),
                        ];
                    }

                    break;
            }
        }
        if ($q_match !== '' && $exact_phrase === null) {
            $q_value = $q_match;
            if ($this->DBEngine === 'InnoDB') {
                $q_value = trim($q_value, '><');
            }
            $search_binds[] = [
                'param' => ':q',
                'value' => $q_value,
            ];
            $q_strip = preg_replace('/(-[\S]+|".+?")/u', '', $q_match);
            $search_binds[] = [
                'param' => ':like_q',
                'value' => '%' . $q_strip . '%',
            ];
        }
        $this->binds = $search_binds;
        $this->op = $search_op;
        $wheres = '';
        switch ($this->type) {
            case 'images':
                if ($exact_phrase !== null) {
                    $this->binds[] = [
                        'param' => ':phrase',
                        'value' => '%' . $exact_phrase . '%',
                    ];
                    $wheres = <<<SQL
                    WHERE (
                        image_name LIKE :phrase
                        OR image_title LIKE :phrase
                        OR image_description LIKE :phrase
                        OR image_original_filename LIKE :phrase
                    )
                    SQL;
                } elseif ($q_match !== '') {
                    $wheres = <<<SQL
                    WHERE (
                        MATCH(`image_name`,`image_title`,`image_description`,`image_original_filename`) AGAINST (:q IN BOOLEAN MODE)
                        OR BINARY image_name LIKE BINARY :like_q
                        OR BINARY image_title LIKE BINARY :like_q
                        OR BINARY image_description LIKE BINARY :like_q
                        OR BINARY image_original_filename LIKE BINARY :like_q
                    )
                    SQL;
                }

                break;
            case 'albums':
                if ($exact_phrase !== null) {
                    $this->binds[] = [
                        'param' => ':phrase',
                        'value' => '%' . $exact_phrase . '%',
                    ];
                    $wheres = <<<SQL
                    WHERE album_name LIKE :phrase
                    SQL;
                } elseif ($q_match !== '') {
                    $wheres = <<<SQL
                    WHERE (
                        MATCH(`album_name`,`album_description`) AGAINST (:q)
                        OR album_name LIKE :like_q
                        OR album_description LIKE :like_q
                    )
                    SQL;
                }

                break;

            case 'users':
                if ($exact_phrase !== null) {
                    $this->binds[] = [
                        'param' => ':phrase',
                        'value' => '%' . $exact_phrase . '%',
                    ];
                    $wheres = <<<SQL
                    WHERE (
                        user_name LIKE :phrase
                        OR user_username LIKE :phrase
                        OR user_email LIKE :phrase
                    )
                    SQL;
                    $clauses = [
                        'name_username' => <<<SQL
                        WHERE (
                            user_name LIKE :phrase
                            OR user_username LIKE :phrase
                        )
                        SQL,
                        'email' => <<<SQL
                        `user_email` LIKE :phrase
                        SQL,
                    ];
                } elseif ($q_match !== '') {
                    $clauses = [
                        'name_username' => <<<SQL
                        WHERE (
                            MATCH(`user_name`,`user_username`) AGAINST (:q)
                            OR BINARY user_name LIKE BINARY :like_q
                            OR BINARY user_username LIKE BINARY :like_q
                        )
                        SQL,
                        'email' => <<<SQL
                        `user_email` LIKE CONCAT("%", :q, "%")
                        SQL,
                    ];
                }
                if (isset($clauses)) {
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
        if ($search_op_wheres !== []) {
            $wheres .= ($wheres === '' ? 'WHERE ' : ' AND ') . implode(' AND ', $search_op_wheres);
        }
        $this->wheres = $wheres ?? '';
        $this->display = [
            'type' => $this->type,
            'q' => $this->q,
            'd' => strlen($this->q) >= 25 ? (substr($this->q, 0, 22) . '...') : $this->q,
        ];
    }

    protected function handleSearchOperators(string $q, bool $full = true): array
    {
        $operators = [
            'any' => [],
            'exact_phrases' => [],
            'excluded' => [],
            'named' => [],
        ];
        $raw_regex = [
            'named' => '[\S]+\:[\S]+', // take all the like:this operators
            'quoted' => '-*[\"\']+.+[\"\']+', // take all the "quoted stuff" "like" "this, one"
            'spaced' => '\S+', // Take all the space separated stuff
        ];
        foreach ($raw_regex as $k => $v) {
            if ($k === 'spaced') {
                $q = str_replace(',', '', $q);
            }
            if (preg_match_all('/' . $v . '/', $q, $match)) {
                foreach ($match[0] as $qMatch) {
                    switch ($k) {
                        case 'named':
                            if (! $full) {
                                $named_operator = explode(':', $qMatch);
                                if (in_array($named_operator[0], self::$excluded, false)) {
                                    continue 2;
                                }
                            }
                            $operators[$k][] = $qMatch;

                            break;
                        default:
                            if (strpos($qMatch, '-') === 0) {
                                $operators['excluded'][] = $qMatch;
                            } elseif (strpos($qMatch, '"') === 0) {
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
