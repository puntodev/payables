<?php


namespace Tests\Feature;


use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Puntodev\Payables\Jobs\StorePayment;
use Tests\TestCase;

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

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'mercado_pago',
            'merchant' => '1',
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals('1', $job->merchant);
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }
}
