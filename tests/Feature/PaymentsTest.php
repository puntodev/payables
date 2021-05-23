<?php


namespace Tests\Feature;


use Mockery\MockInterface;
use Puntodev\Payables\Exceptions\InvalidGateway;
use Puntodev\Payables\Gateways\MercadoPagoGateway;
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

    public function test_fails_if_unknown_gateway()
    {
        $this->expectException(InvalidGateway::class);

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->payments->checkout('wrong', $product, $user);
    }

    /**
     * @test
     */
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
}
