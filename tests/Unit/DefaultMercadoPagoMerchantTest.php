<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Puntodev\Payables\Gateways\MercadoPago\DefaultMercadoPagoMerchant;
use RuntimeException;
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
    public function type()
    {
        $this->expectException(RuntimeException::class);
        $this->merchant->type();
    }

    /** @test */
    public function identifier()
    {
        $this->expectException(RuntimeException::class);
        $this->merchant->identifier();
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
