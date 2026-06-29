<?php

namespace App\Services;

use InvalidArgumentException;
use App\Contracts\PaymentGatewayInterface;

/**
 * Manages payment gateway using the strategy pattern.
 */
class PaymentManager
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    /**
     * Register a payment gateway.
     */
    public function registerGateway(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }

    /**
     * Resolve the appropriate gateway for a given payment method.
     *
     * @throws InvalidArgumentException If no gateway supports the given method.
     */
    public function resolveGateway(string $method): PaymentGatewayInterface
    {
        if (isset($this->gateways[$method])) {
            return $this->gateways[$method];
        }

        foreach ($this->gateways as $gateway) {
            if ($gateway->supportsMethod($method)) {
                return $gateway;
            }
        }

        $available = implode(', ', array_keys($this->gateways));

        throw new InvalidArgumentException(
            "No payment gateway found for method '{$method}'. Available gateways: {$available}."
        );
    }

    /**
     * Get all registered gateway names.
     *
     * @return array<string>
     */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Check if a gateway is registered for the given method.
     */
    public function hasGateway(string $method): bool
    {
        if (isset($this->gateways[$method])) {
            return true;
        }

        foreach ($this->gateways as $gateway) {
            if ($gateway->supportsMethod($method)) {
                return true;
            }
        }

        return false;
    }
}
