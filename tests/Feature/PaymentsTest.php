<?php


namespace Tests\Feature;


use Mockery\MockInterface;
use Puntodev\MercadoPago\Facades\MercadoPago as MercadoPagoFacade;
use Puntodev\MercadoPago\MercadoPagoApi;
use Puntodev\Payables\Exceptions\InvalidGateway;
use Puntodev\Payables\Payments;
use Tests\TestCase;
use Tests\TestMerchant;
use Tests\TestPaymentOrder;

class PaymentsTest extends TestCase
{
    private Payments $payments;

    public function withSandbox(): array
    {
        return [
            'using sandbox' => [true],
            'using production' => [false],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->payments = new Payments();
    }

    public function test_fails_if_unknown_gateway()
    {
        $this->expectException(InvalidGateway::class);

        $po = new TestPaymentOrder();

        $this->payments->checkout('wrong', $po);
    }

    /**
     * @dataProvider withSandbox
     */
    public function test_can_create_payment(bool $usingSandbox)
    {
        /** @var MercadoPagoApi| MockInterface $mock */
        $mock = $this->spy(MercadoPagoApi::class);

        MercadoPagoFacade::shouldReceive('withCredentials')
            ->with('some-client-id', 'some-client-secret')
            ->once()
            ->andReturn($mock);
        MercadoPagoFacade::shouldReceive('usingSandbox')
            ->once()
            ->andReturn($usingSandbox);

        $mock->shouldReceive('createPaymentPreference')
            ->once()
            ->with([
                'items' => [
                    0 =>
                        [
                            'title' => 'some item',
                            'quantity' => 1,
                            'unit_price' => 10.0,
                            'currency' => 'ARS',
                        ],
                ],
                'payer' => [
                    'name' => 'Max',
                    'surname' => 'Speed',
                    'email' => 'example@example.com',
                ],
                'payment_methods' => [],
                'notification_url' => 'https://www.example.com/notification',
                'external_reference' => 'b42f849e-90ad-4d7c-b9f6-e5bc2943b2b0',
                'back_urls' =>
                    [
                        'success' => 'https://www.example.com/success',
                        'pending' => 'https://www.example.com/pending',
                        'failure' => 'https://www.example.com/failure',
                    ],
                'auto_return' => 'all',
                'binary_mode' => true,
                'expires' => false,
            ])
            ->andReturn([
                'id' => 'some-id',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0',
                'init_point' => 'https://mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0',
                'external_reference' => 'b42f849e-90ad-4d7c-b9f6-e5bc2943b2b0',
            ]);

        $po = new TestPaymentOrder();
        $merchant = new TestMerchant();

        $gatewayPaymentOrder = $this->payments->checkout('mercado_pago', $po, $merchant);
        $this->assertEquals('mercado_pago', $gatewayPaymentOrder->gateway());
        $this->assertEquals('some-id', $gatewayPaymentOrder->id());
        $this->assertEquals($usingSandbox ?
            'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0' :
            'https://mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0',
            $gatewayPaymentOrder->redirectLink());
        $this->assertEquals('b42f849e-90ad-4d7c-b9f6-e5bc2943b2b0', $gatewayPaymentOrder->externalId());
    }
}
