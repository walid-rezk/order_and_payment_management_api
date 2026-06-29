<?php

namespace App\Contracts;

use App\DTOs\PaymentResult;
use App\Models\Order;

/**
 * Interface for all payment gateway implementations.
 *
 * To add a new payment gateway:
 * 1. Create a class implementing this interface in App\Services\PaymentGateways
 * 2. Register it in App\Providers\PaymentServiceProvider::boot()
 * 3. Add gateway configuration to config/gateways.php and .env
 */
interface PaymentGatewayInterface
{
    /**
     * Get the unique name/identifier for this gateway.
     */
    public function getName(): string;

    /**
     * Process a payment for the given order.
     */
    public function processPayment(Order $order, float $amount): PaymentResult;

    /**
     * Check if this gateway supports the given payment method.
     */
    public function supportsMethod(string $method): bool;
}
