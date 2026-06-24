<?php

namespace App\Services\Maps;

use App\ValueObjects\Coordinates;
use Illuminate\Support\Facades\Http;

class GoogleMapsProvider implements MapProvider
{
    public function __construct(
        protected readonly ?string $apiKey
    ) {}

    public function quoteRoute(Coordinates $origin, Coordinates $destination): RouteQuoteData
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Google Maps API Key tidak dikonfigurasi.');
        }

        $url = 'https://routes.googleapis.com/directions/v2:computeRoutes';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration',
        ])->post($url, [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude' => $origin->latitude,
                        'longitude' => $origin->longitude,
                    ],
                ],
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude' => $destination->latitude,
                        'longitude' => $destination->longitude,
                    ],
                ],
            ],
            'travelMode' => 'DRIVE',
        ]);

        if ($response->failed()) {
            throw new \Exception('Gagal menghubungi Google Routes API: '.$response->body());
        }

        $data = $response->json();

        if (empty($data['routes'][0])) {
            throw new \Exception('Rute tidak ditemukan oleh Google Maps.');
        }

        $route = $data['routes'][0];
        $distance = (int) ($route['distanceMeters'] ?? 0);

        // Duration is returned as string with 's' suffix, e.g. "450s"
        $durationString = $route['duration'] ?? '0s';
        $duration = (int) rtrim($durationString, 's');

        return new RouteQuoteData(
            distanceMeters: $distance,
            durationSeconds: $duration,
            provider: 'google_maps'
        );
    }
}
