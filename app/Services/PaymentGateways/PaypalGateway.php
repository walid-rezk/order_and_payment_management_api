<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Support\Str;
use App\DTOs\PaymentResult;
use App\Contracts\PaymentGatewayInterface;

/**
 * Simulated PayPal payment gateway.
 */
class PaypalGateway implements PaymentGatewayInterface
{
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        $this->clientId = config('gateways.paypal.client_id', '');
        $this->secret = config('gateways.paypal.secret', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'paypal';
    }

    /**
     * Simulate PayPal payment processing.
     */
    public function processPayment(Order $order, float $amount): PaymentResult
    {
        // Validate gateway configuration
        if (empty($this->clientId) || empty($this->secret)) {
            return PaymentResult::failed('PayPal gateway is not configured.');
        }

        // Simulate payment processing, this would be an API call on production
        $transactionId = 'pp_txn_' . Str::uuid()->toString();

        return PaymentResult::successful(
            transactionId: $transactionId,
            message: "PayPal payment of \${$amount} processed successfully.",
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsMethod(string $method): bool
    {
        return $method === 'paypal';
    }
}
