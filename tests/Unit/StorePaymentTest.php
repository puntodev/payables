<?php

namespace Tests\Unit;


use Mockery\MockInterface;
use Puntodev\Payables\Gateways\MercadoPagoGateway;
use Puntodev\Payables\Jobs\StorePayment;
use Tests\TestCase;

class StorePaymentTest extends TestCase
{
    /** @test */
    public function it_deletage_payment_storage_to_gateway()
    {
        /** @var MockInterface> $spy */
        $spy = $this->spy(MercadoPagoGateway::class);

        $storePayment = new StorePayment('mercado_pago', '1', ['hello' => 'world']);
        $storePayment->handle();

        $spy->shouldHaveReceived('processWebhook');
    }
}
