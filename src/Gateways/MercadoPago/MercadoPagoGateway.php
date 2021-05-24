<?php


namespace Puntodev\Payables\Gateways\MercadoPago;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;
use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\GatewayPaymentOrder;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\Payable;
use Puntodev\Payables\Contracts\PaymentOrder;
use Puntodev\Payables\Contracts\PaymentOrderItem;
use Puntodev\Payables\Helpers\DefaultGatewayPaymentOrder;
use Puntodev\Payables\Models\Order;
use Puntodev\Payables\Models\Payment;
use Ramsey\Uuid\Uuid;

class MercadoPagoGateway implements Gateway
{
    public function __construct(private MercadoPago $mercadoPago)
    {
    }

    public function createOrder(Merchant $merchant, Payable $payable): GatewayPaymentOrder
    {
        $paymentOrder = $payable->toPaymentOrder();
        $order = $this->storeLocalOrder($paymentOrder, $merchant, $payable);

        $paymentPreference = $this->createPaymentPreferenceForOrder($order->uuid, $paymentOrder, $merchant);

        $created = $this->mercadoPago
            ->withCredentials($merchant->clientId(), $merchant->clientSecret())
            ->createPaymentPreference($paymentPreference);

        return new DefaultGatewayPaymentOrder(
            'mercado_pago',
            $created['id'],
            $this->mercadoPago->usingSandbox() ?
                $created['sandbox_init_point'] :
                $created['init_point'],
            $order->uuid,
        );
    }

    public function processWebhook(Merchant $merchant, array $data): void
    {
        $orderId = Arr::get($data, 'data.id');

        $merchantOrder = $this->mercadoPago
            ->withCredentials($merchant->clientId(), $merchant->clientSecret())
            ->findMerchantOrderById($orderId);

        Log::debug("Merchant Order: " . json_encode($merchantOrder));
        Log::debug("External Reference: " . $merchantOrder['external_reference']);

        /** @var Order $order */
        $order = Order::where('uuid', $merchantOrder['external_reference'])
            ->firstOrFail();

        $this->upsertPaymentForMerchantOrder($order, $merchantOrder);
    }

    private function upsertPaymentForMerchantOrder(Order $order, array $merchantOrder)
    {
        if ($this->extractMerchantOrderPaidAmount($merchantOrder) >= $merchantOrder['total_amount']) {
            Log::debug("Totally paid. Release your item.");
        } else {
            Log::debug("Not paid yet. Do not release your item.");
        }

        Payment::updateOrCreate([
            'order_id' => $order->id,
            'payment_reference' => $merchantOrder['id'],
        ], [
            'external_reference' => $merchantOrder['external_reference'],
            'paid_on' => $this->extractMerchantOrderPaidOn($merchantOrder),
            'amount' => $merchantOrder['total_amount'],
            'currency' => 'ARS',
            'status' => $this->mapMerchantOrderStatus($merchantOrder),
            'raw' => $merchantOrder,
        ]);
    }

    private function mapMerchantOrderStatus(array $merchantOrder): string
    {
        $refundedAmount = Arr::get($merchantOrder, 'refunded_amount', 0);
        if ($refundedAmount > 0) {
            $status = Payment::REFUNDED;
        } else {
            $paidAmount = $this->extractMerchantOrderPaidAmount($merchantOrder);
            $totalAmount = Arr::get($merchantOrder, 'total_amount');
            if ($paidAmount - $refundedAmount >= $totalAmount)
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
            ->reduce(fn(float $carry, array $item) => $carry + $item['transaction_amount'], 0.0);
    }

    private function extractMerchantOrderPaidOn(array $merchantOrder): Carbon
    {
        return collect($merchantOrder['payments'])
                ->filter(fn(array $payment) => $payment['date_approved'])
                ->pluck('date_approved')
                ->map(fn(string $approved) => Carbon::parse($approved))
                ->max() ?? Carbon::parse('1969-01-01 00:00:00Z');
    }

    private function storeLocalOrder(PaymentOrder $paymentOrder, Merchant $merchant, Payable $payable): Order
    {
        $order = new Order();
        $order->uuid = Uuid::uuid4();
        $order->payment_method = 'mercado_pago';
        $order->status = Order::CREATED;
        $order->amount = collect($paymentOrder->items())
            ->reduce(fn($carry, $item) => $carry + $item->quantity() * $item->amount(), 0.0);
        $order->currency = $paymentOrder->items()[0]->currency();
        if ($merchant instanceof Model) {
            $order->merchant()->associate($merchant);
        }
        $order->payable()->associate($payable);
        $order->save();

        return $order;
    }

    private function createPaymentPreferenceForOrder(string $externalReference, PaymentOrder $order, Merchant $merchant): array
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

        $notificationUrl = !empty($merchant->type()) && !empty($merchant->identifier()) ?
            URL::route('payments.incoming', [
                'gateway' => 'mercado_pago',
                'merchantType' => $merchant->type(),
                'merchantId' => $merchant->identifier(),
            ]) :
            URL::route('payments.incoming.default', [
                'gateway' => 'mercado_pago',
            ]);

        return $builder
            ->externalId($externalReference)
            ->payerFirstName($order->firstName())
            ->payerLastName($order->lastName())
            ->payerEmail($order->email())
            ->excludedPaymentMethods($order->excludedPaymentMethods())
            ->successBackUrl($order->successBackUrl())
            ->pendingBackUrl($order->pendingBackUrl())
            ->failureBackUrl($order->failureBackUrl())
            ->notificationUrl($notificationUrl)
            ->binaryMode(true)
            ->make();
    }

    public function defaultMerchant(): Merchant
    {
        return new DefaultMercadoPagoMerchant();
    }
}
