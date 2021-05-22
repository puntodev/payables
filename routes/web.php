<?php

use Illuminate\Support\Facades\Route;
use Puntodev\Payables\Http\Controllers\PaymentsWebhookController;

Route::get('/payments/{gateway}', [PaymentsWebhookController::class, 'incoming'])
    ->name('payment.incoming');
