<?php


namespace Tests;


use Puntodev\Payables\Contracts\PaymentOrderItem;

class TestPaymentOrderItem implements PaymentOrderItem
{
    /**
     * TestPaymentOrderItem constructor.
     */
    public function __construct()
    {
    }

    public function amount(): float
    {
        return 10.0;
    }

    public function quantity(): int
    {
        return 1;
    }

    public function currency(): string
    {
        return "ARS";
    }

    public function description(): string
    {
        return "some item";
    }
}
