<?php


namespace Puntodev\Payables\Contracts;


interface Gateway
{
    public function createOrder(PaymentOrder $order, Merchant $merchant): GatewayPaymentOrder;

    public function processWebhook($merchantId, $data);

}
