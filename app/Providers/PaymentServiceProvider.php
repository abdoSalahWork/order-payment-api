<?php

namespace App\Providers;

use App\Services\PaymentGateways\PaymentGatewayFactory;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayFactory::class, function ($app) {
            return new PaymentGatewayFactory();
        });
    }

    public function boot(): void
    {
        //
    }
}
