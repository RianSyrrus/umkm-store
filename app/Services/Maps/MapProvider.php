<?php

namespace App\Services\Maps;

use App\ValueObjects\Coordinates;

interface MapProvider
{
    /**
     * Get route distance and duration between origin and destination coordinates.
     *
     * @throws \Exception
     */
    public function quoteRoute(Coordinates $origin, Coordinates $destination): RouteQuoteData;
}
