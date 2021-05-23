<?php


namespace Tests\Unit;


use Mockery\MockInterface;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\MercadoPagoApi;
use Puntodev\Payables\Contracts\Payable;
use Puntodev\Payables\Gateways\MercadoPagoGateway;
use Puntodev\Payables\Models\Order;
use RuntimeException;
use Tests\Product;
use Tests\TestCase;
use Tests\User;

class MercadoPagoGatewayTest extends TestCase
{
    private MercadoPagoGateway $gateway;
    private MercadoPago|MockInterface $mock;
    private MercadoPagoApi|MockInterface $mockApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApi = $this->mock(MercadoPagoApi::class);

        $this->mock = $this->mock(MercadoPago::class);
        $this->mock->shouldReceive('withCredentials')
            ->with("some-client-id", "some-client-secret")
            ->andReturn($this->mockApi);

        $this->gateway = app(MercadoPagoGateway::class);
    }

    public function sandbox()
    {
        return [
            'using sandbox' => [true, 'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0'],
            'using production' => [false, 'https://mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0'],
        ];
    }

    /**
     * @test
     * @dataProvider sandbox
     */
    public function it_can_create_an_order(bool $usingSandbox, string $expectedRedirectUrl)
    {
        /** @var User $user */
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->mockApi->shouldReceive('createPaymentPreference')
            ->once()
            ->andReturn([
                'id' => 'some-id',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0',
                'init_point' => 'https://mercadopago.com.ar/checkout/v1/redirect?pref_id=539968136-5a869e89-04eb-46cc-9949-373e195dc9e0',
                'external_reference' => 'b42f849e-90ad-4d7c-b9f6-e5bc2943b2b0',
            ]);
        $this->mock->shouldReceive('usingSandbox')
            ->once()
            ->andReturn($usingSandbox);

        $gatewayPaymentOrder = $this->gateway->createOrder($user, $product);

        $this->assertEquals('mercado_pago', $gatewayPaymentOrder->gateway());
        $this->assertEquals('some-id', $gatewayPaymentOrder->id());
        $this->assertEquals($expectedRedirectUrl, $gatewayPaymentOrder->redirectLink());
        $this->assertNotNull($gatewayPaymentOrder->externalId());

        $this->assertDatabaseHas('orders', [
            'payment_method' => 'mercado_pago',
            'merchant_type' => $user->getMorphClass(),
            'merchant_id' => $user->identifier(),
            'payable_type' => $product->getMorphClass(),
            'payable_id' => $product->id,
            'uuid' => $gatewayPaymentOrder->externalId(),
            'status' => Order::CREATED,
            'currency' => 'ARS',
            'amount' => 20000,
        ]);
    }

    /** @test */
    public function it_can_process_webhook()
    {
        /** @var User $merchant */
        $merchant = User::factory()->create();

        /** @var Payable $payable */
        $payable = Product::factory(['amount' => 50]);

        /** @var Order $order */
        $order = Order::factory()
            ->hasMerchant($merchant)
            ->hasPayable($payable)
            ->create(['amount' => 50]);

        $merchantOrderId = "123456";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($merchantOrderId)
            ->andReturn([
                "id" => $merchantOrderId,
                "status" => "closed",
                "external_reference" => $order->uuid,
                "preference_id" => "45c7de28-2fa4-4e6b-bcc9-e00169c2ff1c",
                "payments" => [
                    [
                        "id" => 2546225874,
                        "transaction_amount" => 100,
                        "total_paid_amount" => 100,
                        "shipping_cost" => 0,
                        "currency_id" => "ARS",
                        "status" => "approved",
                        "status_detail" => "accredited",
                        "operation_type" => "regular_payment",
                        "date_approved" => "2021-05-22T11:56:46.000-04:00",
                        "date_created" => "2021-05-22T18:50:16.000-04:00",
                        "last_modified" => "2021-05-22T11:56:46.000-04:00",
                        "amount_refunded" => 0
                    ]
                ],
                "shipments" => [
                ],
                "collector" => [
                    "id" => 99999,
                    "email" => "merchant@example.com",
                    "nickname" => "MERCHANT"
                ],
                "marketplace" => "NONE",
                "notification_url" => "http://localhost:8080/payments/mercado-pago/Tests\\User-1",
                "date_created" => "2021-05-22T22:49:52.666+00:00",
                "last_updated" => "2021-05-22T15:56:46.327+00:00",
                "sponsor_id" => null,
                "shipping_cost" => 0,
                "total_amount" => 100,
                "site_id" => "MLA",
                "paid_amount" => 100,
                "refunded_amount" => 0,
                "payer" => [
                    "id" => 12345,
                    "email" => "example@example.com"
                ],
                "items" => [
                    [
                        "id" => "",
                        "category_id" => "",
                        "currency_id" => "ARS",
                        "description" => "",
                        "picture_url" => "",
                        "title" => "An example of item",
                        "quantity" => 1,
                        "unit_price" => 300
                    ]
                ],
                "cancelled" => false,
                "additional_info" => "",
                "application_id" => null,
                "order_status" => "paid"
            ]);

        $data = [
            "action" => "payment.created",
            "api_version" => "v1",
            "data" => [
                "id" => $merchantOrderId,
            ],
            "date_created" => "2021-05-03T11:50:23Z",
            "id" => 7411039426,
            "live_mode" => true,
            "type" => "payment",
            "user_id" => "361129569",
        ];
        $this->gateway->processWebhook($merchant, $data);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_reference' => $merchantOrderId,
            'paid_on' => '2021-05-22 11:56:46',
            'status' => 'paid',
            'currency' => 'ARS',
            'amount' => 10000,
        ]);
    }

    /** @test */
    public function it_can_process_webhook_insuffient_funds()
    {
        /** @var User $merchant */
        $merchant = User::factory()->create();

        /** @var Payable $payable */
        $payable = Product::factory(['amount' => 50]);

        /** @var Order $order */
        $order = Order::factory()
            ->hasMerchant($merchant)
            ->hasPayable($payable)
            ->create(['amount' => 50]);

        $merchantOrderId = "123456";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($merchantOrderId)
            ->andReturn([
                "id" => $merchantOrderId,
                "status" => "closed",
                "external_reference" => $order->uuid,
                "preference_id" => "45c7de28-2fa4-4e6b-bcc9-e00169c2ff1c",
                "payments" => [
                    [
                        "id" => 2546225874,
                        "transaction_amount" => 50,
                        "total_paid_amount" => 50,
                        "shipping_cost" => 0,
                        "currency_id" => "ARS",
                        "status" => "approved",
                        "status_detail" => "accredited",
                        "operation_type" => "regular_payment",
                        "date_approved" => "2021-05-22T11:56:46.000-04:00",
                        "date_created" => "2021-05-22T18:50:16.000-04:00",
                        "last_modified" => "2021-05-22T11:56:46.000-04:00",
                        "amount_refunded" => 0
                    ]
                ],
                "shipments" => [
                ],
                "collector" => [
                    "id" => 99999,
                    "email" => "merchant@example.com",
                    "nickname" => "MERCHANT"
                ],
                "marketplace" => "NONE",
                "notification_url" => "http://localhost:8080/payments/mercado-pago/Tests\\User-1",
                "date_created" => "2021-05-22T22:49:52.666+00:00",
                "last_updated" => "2021-05-22T15:56:46.327+00:00",
                "sponsor_id" => null,
                "shipping_cost" => 0,
                "total_amount" => 100,
                "site_id" => "MLA",
                "paid_amount" => 100,
                "refunded_amount" => 0,
                "payer" => [
                    "id" => 12345,
                    "email" => "example@example.com"
                ],
                "items" => [
                    [
                        "id" => "",
                        "category_id" => "",
                        "currency_id" => "ARS",
                        "description" => "",
                        "picture_url" => "",
                        "title" => "An example of item",
                        "quantity" => 2,
                        "unit_price" => 50
                    ]
                ],
                "cancelled" => false,
                "additional_info" => "",
                "application_id" => null,
                "order_status" => "paid"
            ]);

        $data = [
            "action" => "payment.created",
            "api_version" => "v1",
            "data" => [
                "id" => $merchantOrderId,
            ],
            "date_created" => "2021-05-03T11:50:23Z",
            "id" => 7411039426,
            "live_mode" => true,
            "type" => "payment",
            "user_id" => "361129569",
        ];
        $this->gateway->processWebhook($merchant, $data);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_reference' => $merchantOrderId,
            'paid_on' => '2021-05-22 11:56:46',
            'status' => 'created',
            'currency' => 'ARS',
            'amount' => 10000,
        ]);
    }

    /** @test */
    public function it_can_process_webhook_refunded()
    {
        /** @var User $merchant */
        $merchant = User::factory()->create();

        /** @var Payable $payable */
        $payable = Product::factory(['amount' => 50]);

        /** @var Order $order */
        $order = Order::factory()
            ->hasMerchant($merchant)
            ->hasPayable($payable)
            ->create(['amount' => 50]);

        $merchantOrderId = "123456";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($merchantOrderId)
            ->andReturn([
                "id" => $merchantOrderId,
                "status" => "closed",
                "external_reference" => $order->uuid,
                "preference_id" => "45c7de28-2fa4-4e6b-bcc9-e00169c2ff1c",
                "payments" => [
                    [
                        "id" => 2546225874,
                        "transaction_amount" => 50,
                        "total_paid_amount" => 50,
                        "shipping_cost" => 0,
                        "currency_id" => "ARS",
                        "status" => "approved",
                        "status_detail" => "accredited",
                        "operation_type" => "regular_payment",
                        "date_approved" => "2021-05-22T11:56:46.000-04:00",
                        "date_created" => "2021-05-22T18:50:16.000-04:00",
                        "last_modified" => "2021-05-22T11:56:46.000-04:00",
                        "amount_refunded" => 0
                    ]
                ],
                "shipments" => [
                ],
                "collector" => [
                    "id" => 99999,
                    "email" => "merchant@example.com",
                    "nickname" => "MERCHANT"
                ],
                "marketplace" => "NONE",
                "notification_url" => "http://localhost:8080/payments/mercado-pago/Tests\\User-1",
                "date_created" => "2021-05-22T22:49:52.666+00:00",
                "last_updated" => "2021-05-22T15:56:46.327+00:00",
                "sponsor_id" => null,
                "shipping_cost" => 0,
                "total_amount" => 100,
                "site_id" => "MLA",
                "paid_amount" => 100,
                "refunded_amount" => 100,
                "payer" => [
                    "id" => 12345,
                    "email" => "example@example.com"
                ],
                "items" => [
                    [
                        "id" => "",
                        "category_id" => "",
                        "currency_id" => "ARS",
                        "description" => "",
                        "picture_url" => "",
                        "title" => "An example of item",
                        "quantity" => 2,
                        "unit_price" => 50
                    ]
                ],
                "cancelled" => false,
                "additional_info" => "",
                "application_id" => null,
                "order_status" => "paid"
            ]);

        $data = [
            "action" => "payment.created",
            "api_version" => "v1",
            "data" => [
                "id" => $merchantOrderId,
            ],
            "date_created" => "2021-05-03T11:50:23Z",
            "id" => 7411039426,
            "live_mode" => true,
            "type" => "payment",
            "user_id" => "361129569",
        ];
        $this->gateway->processWebhook($merchant, $data);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_reference' => $merchantOrderId,
            'paid_on' => '2021-05-22 11:56:46',
            'status' => 'refunded',
            'currency' => 'ARS',
            'amount' => 10000,
        ]);
    }

    /** @test */
    public function it_fails_to_retrieve_order_from_gateway()
    {
        $this->expectException(RuntimeException::class);

        /** @var User $user */
        $user = User::factory()->create();

        $orderId = "14699991675";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($orderId)
            ->andThrow(RuntimeException::class);

        $data = [
            "data" => [
                "id" => $orderId,
            ],
        ];
        $this->gateway->processWebhook($user, $data);
    }
}
