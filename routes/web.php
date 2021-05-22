<?php

use Illuminate\Support\Facades\Route;
use Puntodev\Payables\Http\Controllers\PaymentsWebhookController;

Route::post('/{gateway}/{merchant}', [PaymentsWebhookController::class, 'incoming'])
    ->name('payments.incoming')
    ->where([
        'gateway' => implode('|', array_keys(config('payments.gateways'))),
    ]);
