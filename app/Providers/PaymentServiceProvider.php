<?php

namespace App\Providers;

use App\Services\PaymentManager;
use Illuminate\Support\ServiceProvider;
use App\Services\PaymentGateways\PaypalGateway;
use App\Services\PaymentGateways\CreditCardGateway;

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
     */
    public function boot(): void
    {
        /** @var PaymentManager $manager */
        $manager = $this->app->make(PaymentManager::class);

        $manager->registerGateway(new CreditCardGateway());
        $manager->registerGateway(new PaypalGateway());
    }
}
