<?php

namespace App\Services\Maps;

use App\ValueObjects\Money;

interface DeliveryFeeCalculator
{
    /**
     * Calculate delivery fee based on base fee, fee per kilometer, and distance in meters.
     */
    public function calculate(int $baseFee, int $feePerKilometer, int $distanceMeters): Money;
}
