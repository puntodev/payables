<?php


namespace Tests\Feature;


use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Jobs\StorePayment;
use Tests\TestCase;
use Tests\User;

class PaymentsWebhookControllerTest extends TestCase
{
    /** @test */
    public function it_rejects_webhook_calls_for_unknow_or_disabled_gateways()
    {
        Bus::fake();

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'bitcoin',
            'merchant' => '1',
        ]))
            ->assertNotFound();

        Bus::assertNotDispatched(StorePayment::class);
    }

    /** @test */
    public function it_can_receive_a_webhook_call()
    {
        Bus::fake();

        /** @var Merchant $merchant */
        $merchant = User::factory()->create();

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'mercado_pago',
            'merchant' => $merchant->merchantId(),
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) use ($merchant) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals($merchant->merchantId(), $job->merchant->merchantId());
            $this->assertEquals($merchant->identifier(), $job->merchant->identifier());
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }
}
