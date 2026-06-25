<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Create a payment transaction and return the snap token and redirect URL.
     *
     * @return array{snap_token: string, redirect_url: string}
     */
    public function createTransaction(Order $order): array;
}
