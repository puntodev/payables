<?php

use Illuminate\Support\Facades\Route;
use Puntodev\Payables\Http\Controllers\PaymentsWebhookController;

Route::post('/{gateway}', [PaymentsWebhookController::class, 'incoming'])
    ->name('payment.incoming')
    ->where([
        'gateway' => implode('|', array_keys(config('payments.gateways'))),
    ]);

