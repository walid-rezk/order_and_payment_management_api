<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

/**
 * Manages payment gateway resolution using the strategy pattern.
 *
 * This class acts as a registry/resolver for payment gateways. New gateways
 * are registered here and resolved at runtime based on the payment method.
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
        // First try direct name match
        if (isset($this->gateways[$method])) {
            return $this->gateways[$method];
        }

        // Then check supportsMethod for more flexible matching
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
