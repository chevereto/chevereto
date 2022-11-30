<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\HashId;

use function Chevere\Message\message;
use Chevere\String\AssertString;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use Throwable;

/**
 * Provides encoding/decoding for integer IDs.
 */
final class HashId
{
    private string $alphabet;

    private string $salt;

    private int $padding;

    private string $hash;

    private string $index;

    private array $table;

    private int $base;

    private string $baseString;

    public function __construct(string $salt)
    {
        $this->assertSalt($salt);
        $this->salt = $salt;
        $this->padding = 0;
        $this->alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $index = str_split($this->alphabet, 1);
        $this->hash = hash('sha256', $this->salt);
        $this->table = [];
        for ($n = 0; $n < strlen($this->alphabet); ++$n) {
            $this->table[] = substr($this->hash, $n, 1);
        }
        array_multisort($this->table, SORT_DESC, $index);
        $this->index = implode($index);
        $this->base = strlen($this->index);
        $this->baseString = (string) $this->base;
    }

    public function withPadding(int $padding): self
    {
        $new = clone $this;
        $this->assertPadding($padding);
        $new->padding = $padding;

        return $new;
    }

    public function decode(string $alpha): int
    {
        $out = 0;
        $len = strlen($alpha) - 1;
        for ($i = 0; $i <= $len; ++$i) {
            $bcpow = bcpow($this->baseString, (string) ($len - $i));
            $out = $out + strpos($this->index, substr($alpha, $i, 1)) * $bcpow;
        }
        if ($this->padding > 0) {
            $out = $out / $this->padding;
        }

        return (int) $out;
    }

    public function encode(int $id): string
    {
        if ($this->padding > 0) {
            $id = $id * $this->padding;
        }
        $out = '';
        for ($i = floor(log((float) $id, $this->base)); $i >= 0; --$i) {
            $bcpow = bcpow($this->baseString, (string) $i);
            $start = floor($id / $bcpow) % $this->base;
            $out = $out . substr($this->index, $start, 1);
            $id = $id - ($start * $bcpow);
        }

        return $out;
    }

    private function assertSalt(string $salt): void
    {
        try {
            (new AssertString($salt))
                ->notEmpty()
                ->notCtypeSpace();
        } catch (Throwable) {
            throw new InvalidArgumentException(
                message('Invalid salt provided'),
            );
        }
    }

    private function assertPadding(int $padding): void
    {
        if ($padding < 0) {
            throw new InvalidArgumentException(
                message('Padding must be greater than zero'),
            );
        }
    }
}
