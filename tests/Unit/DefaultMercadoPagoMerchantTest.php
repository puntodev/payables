<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Puntodev\Payables\Gateways\MercadoPago\DefaultMercadoPagoMerchant;
use Tests\TestCase;

class DefaultMercadoPagoMerchantTest extends TestCase
{
    private DefaultMercadoPagoMerchant $merchant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->merchant = new DefaultMercadoPagoMerchant();
    }

    /** @test */
    public function client_id()
    {
        Config::set('mercadopago.client_id', 'some-client-id');
        $this->assertEquals('some-client-id', $this->merchant->clientId());
    }

    /** @test */
    public function client_secret()
    {
        Config::set('mercadopago.client_secret', 'some-client-secret');
        $this->assertEquals('some-client-secret', $this->merchant->clientSecret());
    }
}
