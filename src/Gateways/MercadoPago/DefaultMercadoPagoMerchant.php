<?php


namespace Puntodev\Payables\Gateways\MercadoPago;


use Puntodev\Payables\Contracts\Merchant;
use RuntimeException;
use function config;

class DefaultMercadoPagoMerchant implements Merchant
{
    public function identifier(): string
    {
        throw new RuntimeException("not implemented");
    }

    public function type(): string
    {
        throw new RuntimeException("not implemented");
    }

    public function clientId(): string
    {
        return config('mercadopago.client_id');
    }

    public function clientSecret(): string
    {
        return config('mercadopago.client_secret');
    }
}
