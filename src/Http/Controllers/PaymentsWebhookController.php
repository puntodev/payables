<?php


namespace Puntodev\Payables\Http\Controllers;


use Illuminate\Support\Facades\Log;

class PaymentsWebhookController extends Controller
{
    public function incoming(string $gateway)
    {
        Log::debug('incoming', ['gateway' => $gateway]);
    }
}
