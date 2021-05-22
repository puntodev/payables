<?php


namespace Puntodev\Payables\Gateways;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;
use Puntodev\Payables\Concerns\HasPayments;
use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\GatewayPaymentOrder;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\PaymentOrder;
use Puntodev\Payables\Contracts\PaymentOrderItem;
use Puntodev\Payables\Helpers\DefaultGatewayPaymentOrder;
use Puntodev\Payables\Models\Payment;

class MercadoPagoGateway implements Gateway
{
    public function __construct(private MercadoPago $mercadoPago)
    {
    }

    public function createOrder(PaymentOrder $order, Merchant $merchant): GatewayPaymentOrder
    {
        $builder = new PaymentPreferenceBuilder();

        /** @var PaymentOrderItem $item */
        foreach ($order->items() as $item) {
            $builder->item()
                ->quantity($item->quantity())
                ->unitPrice($item->amount())
                ->currency($item->currency())
                ->title($item->description())
                ->make();
        }

        $paymentPreference = $builder
            ->externalId($order->externalReference())
            ->payerFirstName($order->firstName())
            ->payerLastName($order->lastName())
            ->payerEmail($order->email())
            ->excludedPaymentMethods($order->excludedPaymentMethods())
            ->successBackUrl($order->successBackUrl())
            ->pendingBackUrl($order->pendingBackUrl())
            ->failureBackUrl($order->failureBackUrl())
            ->notificationUrl(URL::route('payments.incoming', [
                'gateway' => 'mercado_pago',
                'merchant' => $merchant->id(),
            ]))
            ->binaryMode(true)
            ->make();

        $created = $this->mercadoPago
            ->withCredentials($merchant->clientId(), $merchant->clientSecret())
            ->createPaymentPreference($paymentPreference);

        return new DefaultGatewayPaymentOrder(
            'mercado_pago',
            $created['id'],
            $this->mercadoPago->usingSandbox() ?
                $created['sandbox_init_point'] :
                $created['init_point'],
            $created['external_reference']
        );
    }

    public function processWebhook($merchantId, $data)
    {
        $orderId = Arr::get($data, 'data.id');

        $merchantOrder = $this->mercadoPago
            ->defaultClient() // TODO Should use merchant's specific client credentials
            ->findMerchantOrderById($orderId);

        Log::debug("Merchant Order: " . json_encode($merchantOrder));
        Log::debug("External Reference: " . $merchantOrder['external_reference']);

        $payment = $this->upsertPaymentForMerchantOrder($merchantId, $merchantOrder);

        $externalReference = $merchantOrder['external_reference'];

        $payable = $this->findPayableForExternalReference($externalReference);
        $payment->payable()->associate($payable);
        $payment->save();
    }

    public function upsertPaymentForMerchantOrder(string $merchantId, array $merchantOrder): Payment
    {
        if ($this->extractMerchantOrderPaidAmount($merchantOrder) >= $merchantOrder['total_amount']) {
            Log::debug("Totally paid. Release your item.");
        } else {
            Log::debug("Not paid yet. Do not release your item.");
        }

        return Payment::updateOrCreate([
            'payment_method' => 'mercado_pago',
            'payment_reference' => $merchantOrder['id'],
        ], [
            'merchant_id' => $merchantId,
            'external_reference' => $merchantOrder['external_reference'],
            'payer_email' => Arr::get($merchantOrder, 'payer.email'),
            'paid_on' => $this->extractMerchantOrderPaidOn($merchantOrder),
            'amount' => $merchantOrder['total_amount'],
            'currency' => 'ARS',
            'status' => $this->mapMerchantOrderStatus($merchantOrder),
            'raw' => $merchantOrder,
        ]);
    }

    private function mapMerchantOrderStatus(array $merchantOrder): string
    {
        $refunded_amount = Arr::get($merchantOrder, 'refunded_amount', 0);
        if ($refunded_amount > 0)
            $status = Payment::REFUNDED;
        else {
            $paidAmount = $this->extractMerchantOrderPaidAmount($merchantOrder);
            $totalAmount = Arr::get($merchantOrder, 'total_amount');
            if ($paidAmount - $refunded_amount >= $totalAmount)
                $status = Payment::PAID;
            else
                $status = Payment::CREATED;
        }
        return $status;
    }

    private function extractMerchantOrderPaidAmount(array $merchantOrder): float
    {
        return collect($merchantOrder['payments'])
            ->filter(fn(array $payment) => $payment['status'] === 'approved')
            ->reduce(fn($carry, array $item) => $carry + $item['transaction_amount'], 0);
    }

    private function extractMerchantOrderPaidOn(array $merchantOrder): Carbon
    {
        return collect($merchantOrder['payments'])
                ->filter(fn(array $payment) => $payment['date_approved'])
                ->pluck('date_approved')
                ->map(fn(string $approved) => Carbon::parse($approved))
                ->max() ?? Carbon::parse('1969-01-01 00:00:00Z');
    }

    private function findPayableForExternalReference(string $externalReference): HasPayments|Model
    {

    }
}
