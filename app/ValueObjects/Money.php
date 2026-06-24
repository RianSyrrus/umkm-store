<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(private int $rupiah) {}

    public static function fromRupiah(int $rupiah): self
    {
        if ($rupiah < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        return new self($rupiah);
    }

    public function add(self $other): self
    {
        return new self($this->rupiah + $other->rupiah);
    }

    public function rupiah(): int
    {
        return $this->rupiah;
    }

    public function format(): string
    {
        return 'Rp'.number_format($this->rupiah, 0, ',', '.');
    }
}
