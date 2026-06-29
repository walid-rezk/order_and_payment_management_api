<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResult;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Simulated credit card payment gateway.
 */
class CreditCardGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $secret;

    public function __construct()
    {
        $this->apiKey = config('gateways.credit_card.api_key', '');
        $this->secret = config('gateways.credit_card.secret', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'credit_card';
    }

    /**
     * Simulate credit card payment processing.
     *
     * In a real implementation, this would call an external API
     * using $this->apiKey and $this->secret for authentication.
     */
    public function processPayment(Order $order, float $amount): PaymentResult
    {
        // Validate gateway configuration
        if (empty($this->apiKey) || empty($this->secret)) {
            return PaymentResult::failed('Credit card gateway is not configured.');
        }

        // Simulate processing — in production, this would be an API call
        $transactionId = 'cc_txn_' . Str::uuid()->toString();

        return PaymentResult::successful(
            transactionId: $transactionId,
            message: "Credit card payment of \${$amount} processed successfully.",
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsMethod(string $method): bool
    {
        return $method === 'credit_card';
    }
}
