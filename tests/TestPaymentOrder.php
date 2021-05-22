<?php


namespace Tests;


use Carbon\Carbon;
use DateTime;
use Puntodev\Payables\Contracts\PaymentOrder;

class TestPaymentOrder implements PaymentOrder
{
    public function items(): array
    {
        return [
            new TestPaymentOrderItem(),
        ];
    }

    public function externalReference(): string
    {
        return "b42f849e-90ad-4d7c-b9f6-e5bc2943b2b0";
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

    public function notificationUrl(): string
    {
        return "https://www.example.com/notification";
    }

    public function expiration(): DateTime|null
    {
        return Carbon::now();
    }
}
