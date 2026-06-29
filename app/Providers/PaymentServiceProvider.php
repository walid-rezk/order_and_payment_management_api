<?php

namespace App\Providers;

use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PaypalGateway;
use App\Services\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function () {
            return new PaymentManager();
        });
    }

    /**
     * Bootstrap services.
     *
     * Register all payment gateways here. To add a new gateway:
     * 1. Create a class implementing PaymentGatewayInterface
     * 2. Add a $manager->registerGateway(new YourGateway()) line below
     * 3. Add configuration to config/gateways.php
     */
    public function boot(): void
    {
        /** @var PaymentManager $manager */
        $manager = $this->app->make(PaymentManager::class);

        $manager->registerGateway(new CreditCardGateway());
        $manager->registerGateway(new PaypalGateway());
    }
}
