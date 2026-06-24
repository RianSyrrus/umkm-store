<?php

use App\Services\Maps\StandardDeliveryFeeCalculator;

test('calculates correct delivery fee with rounded up kilometers', function () {
    $calculator = new StandardDeliveryFeeCalculator;

    // 1. Distance: 2500m (2.5 km -> rounded up to 3 km)
    // Base Fee: 5000, Fee per km: 2000
    // Total: 5000 + (3 * 2000) = 11000
    $fee = $calculator->calculate(5000, 2000, 2500);

    expect($fee->rupiah())->toBe(11000);

    // 2. Distance: 950m (0.95 km -> rounded up to 1 km)
    // Total: 5000 + (1 * 2000) = 7000
    $fee2 = $calculator->calculate(5000, 2000, 950);

    expect($fee2->rupiah())->toBe(7000);

    // 3. Distance: 10000m (10 km -> exactly 10 km)
    // Total: 5000 + (10 * 2000) = 25000
    $fee3 = $calculator->calculate(5000, 2000, 10000);

    expect($fee3->rupiah())->toBe(25000);
});
