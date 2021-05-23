<?php


namespace Puntodev\Payables\Contracts;


interface Gateway
{
    public function createOrder(Merchant $merchant, Payable $payable): GatewayPaymentOrder;

    public function processWebhook(Merchant $merchant, array $data): void;
}
