<?php

use App\ValueObjects\OrderCode;

test('generates valid non-sequential order code matching UMK-YYYYMMDD-XXXXXX pattern', function () {
    $code = OrderCode::generate();
    expect((string) $code)->toMatch('/^UMK-\d{8}-[A-Z0-9]{6}$/');
});

test('instantiates from valid string format', function () {
    $raw = 'UMK-20260625-AB12CD';
    $code = OrderCode::from($raw);
    expect((string) $code)->toBe($raw);
});

test('throws exception for invalid order code formats', function () {
    OrderCode::from('INVALID-CODE');
})->throws(InvalidArgumentException::class);
