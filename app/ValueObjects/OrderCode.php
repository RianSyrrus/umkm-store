<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class OrderCode
{
    private function __construct(private string $value) {}

    /**
     * Generate a random non-sequential order code matching UMK-YYYYMMDD-XXXXXX pattern.
     */
    public static function generate(): self
    {
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex characters

        return new self("UMK-{$date}-{$random}");
    }

    /**
     * Create an OrderCode instance from a raw string.
     */
    public static function from(string $value): self
    {
        if (! preg_match('/^UMK-\d{8}-[A-Z0-9]{6}$/', $value)) {
            throw new InvalidArgumentException('Invalid order code format.');
        }

        return new self($value);
    }

    /**
     * Get the string representation of the order code.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
