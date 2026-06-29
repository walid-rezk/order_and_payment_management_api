<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentManager;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\Paymentgateways\PayPalGateway;
use App\Models\Order;
use InvalidArgumentException;

class PaymentGatewayStrategyTest extends TestCase
{
    public function test_it_resolves_credit_card_gateway_correctly()
    {
        $manager = $this->app->make(PaymentManager::class);
        $gateway = $manager->resolveGateway('credit_card');

        $this->assertInstanceOf(CreditCardGateway::class, $gateway);
    }

    public function test_it_resolves_paypal_gateway_correctly()
    {
        $manager = $this->app->make(PaymentManager::class);
        $gateway = $manager->resolveGateway('paypal');

        $this->assertInstanceOf(PayPalGateway::class, $gateway);
    }

    public function test_it_throws_exception_for_unsupported_gateway()
    {
        $this->expectException(InvalidArgumentException::class);

        $manager = $this->app->make(PaymentManager::class);
        $manager->resolveGateway('unsupported_gateway');
    }

    public function test_it_successfully_processes_mocked_payment_strategy()
    {
        $order = Order::factory()->make(['total' => 150.00]);

        $manager = $this->app->make(PaymentManager::class);
        $gateway = $manager->resolveGateway('credit_card');

        $result = $gateway->processPayment($order, $order->total);

        $this->assertNotNull($result->transactionId);
    }
}
