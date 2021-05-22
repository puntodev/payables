<?php

namespace Puntodev\Payables;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\Payables\Gateways\MercadoPagoGateway;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('CreatePaymentsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_payments_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_payments_table.php'),
                ], 'migrations');
            }
        }

        $this->registerRoutes();
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

        // Register the main class to use with the facade
        $this->app->singleton(MercadoPagoGateway::class, function (Application $app) {
            return new MercadoPagoGateway($app->make(MercadoPago::class));
        });
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('payments.prefix'),
            'middleware' => config('payments.middleware'),
        ];
    }
}
