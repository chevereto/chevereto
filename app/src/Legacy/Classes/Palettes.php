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

class Palettes
{
    private array $handles = [
        0 => 'blanco',
        1 => 'dark',
        2 => 'flickr',
        3 => 'imgur',
        4 => 'deviantart',
        5 => 'lush',
        6 => 'graffiti',
        7 => 'abstract',
        8 => 'cheers',
        9 => 'cmyk',
    ];

    private array $names = [
        0 => 'Blanco',
        1 => 'Dark',
        2 => 'Flickr',
        3 => 'Imgur',
        4 => 'DeviantArt',
        5 => 'Lush',
        6 => 'Graffiti',
        7 => 'Abstract',
        8 => 'Cheers',
        9 => 'CMYK',
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
