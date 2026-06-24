<?php

use App\ValueObjects\Money;

test('adds and formats rupiah', function () {
    $total = Money::fromRupiah(10000)->add(Money::fromRupiah(2500));

    expect($total->rupiah())->toBe(12500)
        ->and($total->format())->toBe('Rp12.500');
});

test('rejects negative public money', function () {
    Money::fromRupiah(-1);
})->throws(InvalidArgumentException::class);
