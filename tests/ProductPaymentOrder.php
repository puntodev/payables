<?php


namespace Tests;


use Carbon\Carbon;
use DateTime;
use Puntodev\Payables\Contracts\PaymentOrder;

class ProductPaymentOrder implements PaymentOrder
{
    /**
     * ProductPaymentOrder constructor.
     */
    public function __construct(private Product $product)
    {
    }

    public function items(): array
    {
        return [
            new ProductPaymentOrderItem($this->product),
        ];
    }

    public function email(): string
    {
        return "example@example.com";
    }

    public function firstName(): string
    {
        return "Max";
    }

    public function lastName(): string
    {
        return "Speed";
    }

    public function excludedPaymentMethods(): array
    {
        return [];
    }

    public function successBackUrl(): string
    {
        return "https://www.example.com/success";
    }

    public function failureBackUrl(): string
    {
        return "https://www.example.com/failure";
    }

    public function pendingBackUrl(): string
    {
        return "https://www.example.com/pending";
    }

    public function expiration(): DateTime|null
    {
        return Carbon::now();
    }
}
