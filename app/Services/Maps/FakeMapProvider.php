<?php

namespace App\Services\Maps;

use App\ValueObjects\Coordinates;

class FakeMapProvider implements MapProvider
{
    public function quoteRoute(Coordinates $origin, Coordinates $destination): RouteQuoteData
    {
        $distance = $origin->distanceTo($destination);

        // Assume average local speed of 30 km/h (approx 8.33 m/s)
        $averageSpeed = 8.33;
        $duration = (int) round($distance / $averageSpeed);

        return new RouteQuoteData(
            distanceMeters: $distance,
            durationSeconds: $duration,
            provider: 'fake_maps'
        );
    }
}
