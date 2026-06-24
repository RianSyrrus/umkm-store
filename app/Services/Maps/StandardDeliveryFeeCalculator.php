<?php

namespace App\Services\Maps;

use App\ValueObjects\Money;

class StandardDeliveryFeeCalculator implements DeliveryFeeCalculator
{
    public function calculate(int $baseFee, int $feePerKilometer, int $distanceMeters): Money
    {
        $billableKm = (int) ceil($distanceMeters / 1000.0);
        $totalFee = $baseFee + ($billableKm * $feePerKilometer);

        return Money::fromRupiah($totalFee);
    }
}
