<?php

namespace Tests\Unit;


use Mockery\MockInterface;
use Puntodev\Payables\Gateways\MercadoPagoGateway;
use Puntodev\Payables\Jobs\StorePayment;
use Tests\TestCase;
use Tests\User;

class StorePaymentTest extends TestCase
{
    /** @test */
    public function it_delegates_payment_storage_to_gateway()
    {
        /** @var MockInterface> $spy */
        $spy = $this->spy(MercadoPagoGateway::class);

        $user = User::factory()->create();

        $storePayment = new StorePayment('mercado_pago', $user, ['hello' => 'world']);
        $storePayment->handle();

        $spy->shouldHaveReceived('processWebhook');
    }
}
