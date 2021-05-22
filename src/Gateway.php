<?php


namespace Puntodev\Payables;


interface Gateway
{
    public function createOrder(PaymentOrder $order, Merchant $merchant);
}
