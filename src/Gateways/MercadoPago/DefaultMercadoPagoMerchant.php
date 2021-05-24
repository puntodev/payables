<?php


namespace Puntodev\Payables\Gateways\MercadoPago;


use Puntodev\Payables\Contracts\Merchant;

class DefaultMercadoPagoMerchant implements Merchant
{
    public function clientId(): string
    {
        return config('mercadopago.client_id');
    }

    public function clientSecret(): string
    {
        return config('mercadopago.client_secret');
    }
}
