<?php


namespace Puntodev\Payables\Http\Controllers;


use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Gateways\MercadoPago\DefaultMercadoPagoMerchant;
use Puntodev\Payables\Jobs\StorePayment;

class PaymentsWebhookController extends Controller
{
    public function incoming(Request $request, string $gateway, string $merchantType, string $merchantId)
    {
        Log::debug('incoming', ['gateway' => $gateway, 'merchant' => $merchantId]);

        $merchant = $this->findMerchant($merchantType, $merchantId);

        StorePayment::dispatch($gateway, $merchant, $request->toArray());
    }

    public function incomingDefault(Request $request, string $gateway)
    {
        Log::debug('incoming', ['gateway' => $gateway]);

        StorePayment::dispatch($gateway, new DefaultMercadoPagoMerchant(), $request->toArray());
    }

    public function findMerchant(string $merchantType, string $merchantId): Merchant
    {
        if (class_exists($merchantType)) {
            return $merchantType::find($merchantId);
        }

        return [Relation::getMorphedModel($merchantType), 'find']($merchantId);
    }
}
