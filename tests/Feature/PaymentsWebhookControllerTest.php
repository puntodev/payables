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

    /**
     * @test
     * @dataProvider useMorphMap
     */
    public function it_can_receive_a_webhook_call(bool $useMorphMap)
    {
        Bus::fake();

        if ($useMorphMap) {
            Relation::morphMap([
                'user' => User::class,
            ]);
        }

        /** @var Merchant $merchant */
        $merchant = User::factory()->create();

        $this->post(URL::route('payments.incoming', [
            'gateway' => 'mercado_pago',
            'merchantType' => $merchant->getMorphClass(),
            'merchantId' => $merchant->id,
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) use ($merchant) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals($merchant->getMorphClass(), $job->merchant->getMorphClass());
            $this->assertEquals($merchant->id, $job->merchant->id);
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }


    /**
     * @test
     * @dataProvider useMorphMap
     */
    public function it_can_receive_a_webhook_call_for_default_merchant(bool $useMorphMap)
    {
        $this->withoutExceptionHandling();
        Bus::fake();

        $this->post(URL::route('payments.incoming.default', [
            'gateway' => 'mercado_pago',
        ]), [
            'hello' => 'world',
        ])
            ->assertOk();

        Bus::assertDispatched(StorePayment::class, function (StorePayment $job) {
            $this->assertEquals('mercado_pago', $job->gateway);
            $this->assertEquals([
                'hello' => 'world',
            ], $job->data);
            return true;
        });
    }

    public function useMorphMap()
    {
        return [
            'not using morphMap' => [false],
            'using morphMap' => [true],
        ];
    }
}
