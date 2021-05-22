<?php


namespace Tests\Unit;


use Mockery\MockInterface;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\MercadoPagoApi;
use Puntodev\Payables\Gateways\MercadoPagoGateway;
use RuntimeException;
use Tests\TestCase;

class MercadoPagoGatewayTest extends TestCase
{
    private MercadoPagoGateway $gateway;
    private MercadoPagoApi|MockInterface $mockApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApi = $this->mock(MercadoPagoApi::class);
        $this->mock(MercadoPago::class)
            ->shouldReceive('defaultClient')
            ->andReturn($this->mockApi);

        $this->gateway = app(MercadoPagoGateway::class);
    }

    /** @test */
    public function it_can_process_webhook()
    {
        $orderId = "14699991675";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($orderId)
            ->andReturn([
                "id" => $orderId,
                "status" => "closed",
                "external_reference" => "e9d898a1-4ce1-4a50-be2b-467f76160ecf",
                "preference_id" => "45c7de28-2fa4-4e6b-bcc9-e00169c2ff1c",
                "payments" => [
                    [
                        "id" => 2546225874,
                        "transaction_amount" => 300,
                        "total_paid_amount" => 300,
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
                "notification_url" => "http://localhost:8080/payments/mercado-pago/1",
                "date_created" => "2021-05-22T22:49:52.666+00:00",
                "last_updated" => "2021-05-22T15:56:46.327+00:00",
                "sponsor_id" => null,
                "shipping_cost" => 0,
                "total_amount" => 300,
                "site_id" => "MLA",
                "paid_amount" => 300,
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

        $merchantId = '1';
        $data = [
            "action" => "payment.updated",
            "api_version" => "v1",
            "data" => [
                "id" => $orderId,
            ],
            "date_created" => "2021-05-03T11:50:23Z",
            "id" => 7411039426,
            "live_mode" => true,
            "type" => "payment",
            "user_id" => "361129569",
        ];
        $this->gateway->processWebhook($merchantId, $data);
    }

    /** @test */
    public function it_fails_to_retrieve_order_from_gateway()
    {
        $this->expectException(RuntimeException::class);
        $orderId = "14699991675";

        $this->mockApi->shouldReceive('findMerchantOrderById')
            ->with($orderId)
            ->andThrow(RuntimeException::class);

        $merchantId = '1';
        $data = [
            "data" => [
                "id" => $orderId,
            ],
        ];
        $this->gateway->processWebhook($merchantId, $data);
    }
}
