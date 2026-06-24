<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class NormalizedPhone
{
    private function __construct(private string $value) {}

    public static function from(string $phone): self
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (! preg_match('/^628\d{8,11}$/', $digits)) {
            throw new InvalidArgumentException('Invalid Indonesian phone number.');
        }

        return new self($digits);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
