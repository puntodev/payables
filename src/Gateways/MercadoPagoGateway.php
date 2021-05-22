<?php


namespace Puntodev\Payables\Gateways;


use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;
use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\GatewayPaymentOrder;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\PaymentOrder;
use Puntodev\Payables\Contracts\PaymentOrderItem;
use Puntodev\Payables\Helpers\DefaultGatewayPaymentOrder;

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
            ->notificationUrl($order->notificationUrl())
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
}
