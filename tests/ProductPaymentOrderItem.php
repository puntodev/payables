<?php


namespace Tests;


use Puntodev\Payables\Contracts\PaymentOrderItem;

class ProductPaymentOrderItem implements PaymentOrderItem
{
    /**
     * TestPaymentOrderItem constructor.
     */
    public function __construct(private Product $product)
    {
    }

    public function amount(): float
    {
        return $this->product->amount;
    }

    public function quantity(): int
    {
        return 2;
    }

    public function currency(): string
    {
        return "ARS";
    }

    public function description(): string
    {
        return $this->product->name;
    }
}
