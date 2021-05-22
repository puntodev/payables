<?php


namespace Puntodev\Payables\Gateways;


use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;
use Puntodev\Payables\Gateway;
use Puntodev\Payables\Merchant;
use Puntodev\Payables\PaymentOrder;

class MercadoPagoGateway implements Gateway
{
    public function __construct(private MercadoPago $mercadoPago)
    {
    }

    public function createOrder(PaymentOrder $order, Merchant $merchant)
    {
        $paymentPreference = (new PaymentPreferenceBuilder())
            ->item()
            ->quantity(1)
            ->unitPrice($order->amount())
            ->currency($order->currency())
            ->title($order->description())
            ->make()
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
    }
}
