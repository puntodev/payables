<?php


namespace Puntodev\Payables\Contracts;


interface Payable
{
    public function toPaymentOrder(): PaymentOrder;
}
