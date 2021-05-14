<?php

namespace Puntodev\Payables;

use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreatePaymentsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_payments_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_payments_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
//        // Automatically apply the package configuration
//        $this->mergeConfigFrom(__DIR__ . '/../config/mercadopago.php', 'mercadopago');
//
//        // Register the main class to use with the facade
//        $this->app->singleton(MercadoPago::class, function ($app) {
//            $clientKey = config('mercadopago.client_id');
//            $clientSecret = config('mercadopago.client_secret');
//            $useSandbox = config('mercadopago.use_sandbox');
//
//            return new MercadoPago(
//                $clientKey,
//                $clientSecret,
//                $useSandbox
//            );
//        });
//        $this->app->alias(MercadoPago::class, 'mercadopago');
    }
}
