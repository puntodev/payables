<?php


namespace Tests\Feature;


use Mockery\MockInterface;
use Puntodev\Payables\Exceptions\InvalidGateway;
use Puntodev\Payables\Facades\Payments as PaymentsFacade;
use Puntodev\Payables\Gateways\MercadoPago\MercadoPagoGateway;
use Puntodev\Payables\Payments;
use Tests\Product;
use Tests\TestCase;
use Tests\User;

class PaymentsTest extends TestCase
{
    private Payments $payments;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payments = new Payments();
    }

    /** @test */
    public function it_fails_if_unknown_gateway()
    {
        $this->expectException(InvalidGateway::class);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->payments->checkout('wrong', $product, $user);
    }

    /** @test */
    public function it_fails_if_unknown_gateway_using_facade()
    {
        $this->expectException(InvalidGateway::class);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        PaymentsFacade::checkout('wrong', $product, $user);
    }

    /** @test */
    public function it_delegates_to_gateway()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $product = Product::factory()->create();

        /** @var MercadoPagoGateway|MockInterface $mock */
        $spy = $this->spy(MercadoPagoGateway::class);

        $this->payments->checkout('mercado_pago', $product, $user);

        $spy->shouldHaveReceived('createOrder');
    }

    /** @test */
    public function it_delegates_to_gateway_with_default_merchant()
    {
        $product = Product::factory()->create();

        /** @var MercadoPagoGateway|MockInterface $mock */
        $spy = $this->spy(MercadoPagoGateway::class);

        $this->payments->checkoutForDefaultMerchant('mercado_pago', $product);

        $spy->shouldHaveReceived('createOrder');
    }
}
