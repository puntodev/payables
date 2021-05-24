<?php

use Illuminate\Support\Facades\Route;
use Puntodev\Payables\Http\Controllers\PaymentsWebhookController;

Route::post('/{gateway}/{merchantType}/{merchantId}', [PaymentsWebhookController::class, 'incoming'])
    ->name('payments.incoming')
    ->where([
        'gateway' => implode('|', array_keys(config('payments.gateways'))),
    ]);

Route::post('/{gateway}', [PaymentsWebhookController::class, 'incomingDefault'])
    ->name('payments.incoming.default')
    ->where([
        'gateway' => implode('|', array_keys(config('payments.gateways'))),
    ]);
