<?php


namespace Puntodev\Payables;


use DateTime;

interface PaymentOrder
{
    public function amount(): float;

    public function currency(): string;

    public function externalReference(): string;

    public function description(): string;

    public function email(): string;

    public function firstName(): string;

    public function lastName(): string;

    public function excludedPaymentMethods(): array;

    public function successBackUrl(): string;

    public function failureBackUrl(): string;

    public function pendingBackUrl(): string;

    public function notificationUrl(): string;

    public function expiration(): DateTime | null;
}
