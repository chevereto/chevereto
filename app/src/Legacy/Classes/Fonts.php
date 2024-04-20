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

class Fonts
{
    private array $handles = [
        0 => 'Helvetica, Arial, sans-serif',
        1 => '"Times New Roman", Times, serif',
        2 => 'Georgia, serif',
        3 => 'Tahoma, Verdana, sans-serif',
        4 => '"Trebuchet MS", Helvetica, sans-serif',
        5 => 'Geneva, Verdana, sans-serif',
        6 => '"Courier New", Courier, monospace',
        7 => '"Brush Script MT", cursive',
        8 => 'Copperplate, Papyrus, fantasy',
    ];

    private array $names = [
        0 => 'Helvetica, Arial, sans-serif',
        1 => 'Times New Roman, Times, serif',
        2 => 'Georgia, serif',
        3 => 'Tahoma, Verdana, sans-serif',
        4 => 'Trebuchet MS, Helvetica, sans-serif',
        5 => 'Geneva, Verdana, sans-serif',
        6 => 'Courier New, Courier, monospace',
        7 => 'Brush Script MT, cursive',
        8 => 'Copperplate, Papyrus, fantasy',
    ];

    private array $get = [];

    private array $handlesToId = [];

    public function __construct()
    {
        $this->handlesToId = array_flip($this->handles);
        foreach ($this->handles as $id => $handle) {
            $this->get[$id] = [$handle, $this->names[$id]];
        }
    }

    public function handlesToId(): array
    {
        return $this->handlesToId;
    }

    public function get(): array
    {
        return $this->get;
    }

    public function getHandle(int $id): string
    {
        return $this->get()[$id][0] ?? '';
    }

    public function getName(int $id): string
    {
        return $this->get()[$id][1] ?? '';
    }

    public function getIdForHandle(string $handle): int
    {
        return $this->handlesToId[$handle] ?? 0;
    }
}
