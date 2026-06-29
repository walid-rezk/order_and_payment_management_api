<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResult;
use App\Models\Order;
use Illuminate\Support\Str;

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
     *
     * In a real implementation, this would use the PayPal SDK
     * with $this->clientId and $this->secret for OAuth.
     */
    public function processPayment(Order $order, float $amount): PaymentResult
    {
        // Validate gateway configuration
        if (empty($this->clientId) || empty($this->secret)) {
            return PaymentResult::failed('PayPal gateway is not configured.');
        }

        // Simulate processing — in production, this would be an API call
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
