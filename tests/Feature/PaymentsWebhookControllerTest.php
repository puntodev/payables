<?php


namespace Tests\Feature;


use Illuminate\Database\Eloquent\Relations\Relation;
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
            'merchantType' => User::getActualClassNameForMorph(User::class),
            'merchantId' => '1',
        ]))
            ->assertNotFound();

        Bus::assertNotDispatched(StorePayment::class);
    }

    /** @test */
    public function it_can_receive_a_webhook_call()
    {
        Bus::fake();

//        Relation::morphMap([
//            'user' => User::class,
//        ]);

        /** @var Merchant $merchant */
        $merchant = User::factory()->create();

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'mercado_pago',
            'merchantType' => $merchant->type(),
            'merchantId' => $merchant->identifier(),
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) use ($merchant) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals($merchant->type(), $job->merchant->type());
            $this->assertEquals($merchant->identifier(), $job->merchant->identifier());
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }

    /** @test */
    public function it_can_receive_a_webhook_call_using_morph_map()
    {
        Bus::fake();

        Relation::morphMap([
            'user' => User::class,
        ]);

        /** @var Merchant $merchant */
        $merchant = User::factory()->create();

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'mercado_pago',
            'merchantType' => $merchant->type(),
            'merchantId' => $merchant->identifier(),
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) use ($merchant) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals($merchant->type(), $job->merchant->type());
            $this->assertEquals($merchant->identifier(), $job->merchant->identifier());
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }
}
