<?php


namespace Puntodev\Payables\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Jobs\StorePayment;

class PaymentsWebhookController extends Controller
{
    public function incoming(Request $request, string $gateway, string $merchantId)
    {
        Log::debug('incoming', ['gateway' => $gateway, 'merchant' => $merchantId]);

        $merchant = $this->findByMerchantId($merchantId);

        StorePayment::dispatch($gateway, $merchant, $request->toArray());
    }

    public function findByMerchantId(string $merchantId): Merchant
    {
        // TODO See if there's a better way
        $explode = explode('-', $merchantId);
        return $explode[0]::find($explode[1]);
    }
}
