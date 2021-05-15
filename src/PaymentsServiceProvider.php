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
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/payments.php', 'payments');

        // Register the main class to use with the facade
        $this->app->singleton(Payments::class, function ($app) {
            return new Payments();
        });
        $this->app->alias(Payments::class, 'payments');
    }
}
