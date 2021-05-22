<?php


namespace Puntodev\Payables\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Puntodev\Payables\Jobs\StorePayment;

class PaymentsWebhookController extends Controller
{
    public function incoming(Request $request, string $gateway, string $merchant)
    {
        Log::debug('incoming', ['gateway' => $gateway, 'merchant' => $merchant]);

        StorePayment::dispatch($gateway, $merchant, $request->toArray());
    }
}
