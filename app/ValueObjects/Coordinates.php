<?php

namespace App\ValueObjects;

class Coordinates
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new \InvalidArgumentException('Latitude harus bernilai antara -90 dan 90.');
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new \InvalidArgumentException('Longitude harus bernilai antara -180 dan 180.');
        }
    }

    public static function from(float $latitude, float $longitude): self
    {
        return new self($latitude, $longitude);
    }

    /**
     * Calculate straight-line distance to another coordinate in meters using Haversine formula.
     */
    public function distanceTo(self $other): int
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return (int) round($angle * $earthRadius);
    }
}
