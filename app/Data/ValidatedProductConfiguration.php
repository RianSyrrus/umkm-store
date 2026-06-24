<?php

namespace App\Data;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ValidatedProductConfiguration
{
    public function __construct(
        public ProductVariant $variant,
        public Collection $options, // Collection of OptionValue
        public Collection $addons, // Collection of Addon
        public int $quantity,
        public int $unitPrice,
        public int $totalPrice
    ) {}
}
