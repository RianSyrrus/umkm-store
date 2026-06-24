<?php

use App\ValueObjects\Coordinates;

test('coordinates value object holds latitude and longitude', function () {
    $coords = Coordinates::from(-6.9174639, 107.6191228);

    expect($coords->latitude)->toBe(-6.9174639)
        ->and($coords->longitude)->toBe(107.6191228);
});

test('coordinates validation throws exception for invalid values', function () {
    expect(fn () => Coordinates::from(-95.0, 100.0))
        ->toThrow(InvalidArgumentException::class, 'Latitude harus bernilai antara -90 dan 90.');

    expect(fn () => Coordinates::from(0.0, 190.0))
        ->toThrow(InvalidArgumentException::class, 'Longitude harus bernilai antara -180 dan 180.');
});

test('calculates correct straight-line distance using haversine formula', function () {
    // Bandung Coordinates
    $origin = Coordinates::from(-6.9174639, 107.6191228); // Bandung City Center
    $destination = Coordinates::from(-6.9274639, 107.6291228); // Approx 1.5 km away

    $distance = $origin->distanceTo($destination);

    // Assert distance is around 1550-1600 meters
    expect($distance)->toBeGreaterThan(1500)
        ->and($distance)->toBeLessThan(1650);
});
