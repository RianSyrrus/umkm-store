<?php

namespace App\Services\Maps;

class RouteQuoteData
{
    public function __construct(
        public readonly int $distanceMeters,
        public readonly int $durationSeconds,
        public readonly string $provider
    ) {}
}
