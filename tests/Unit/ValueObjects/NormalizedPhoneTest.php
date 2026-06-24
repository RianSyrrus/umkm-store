<?php

use App\ValueObjects\NormalizedPhone;

test('normalizes Indonesian WhatsApp numbers', function () {
    expect((string) NormalizedPhone::from('0812-3456-7890'))->toBe('6281234567890')
        ->and((string) NormalizedPhone::from('081234567890'))->toBe('6281234567890')
        ->and((string) NormalizedPhone::from('+6281234567890'))->toBe('6281234567890')
        ->and((string) NormalizedPhone::from('6281234567890'))->toBe('6281234567890')
        ->and((string) NormalizedPhone::from('81234567890'))->toBe('6281234567890');
});

test('rejects invalid phone numbers', function () {
    NormalizedPhone::from('123456');
})->throws(InvalidArgumentException::class);
