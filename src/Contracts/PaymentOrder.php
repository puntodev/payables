<?php


namespace Puntodev\Payables\Contracts;


use DateTime;

interface PaymentOrder
{
    public function items(): array;

    public function externalReference(): string;

    public function email(): string;

    public function firstName(): string;

    public function lastName(): string;

    public function excludedPaymentMethods(): array;

    public function successBackUrl(): string;

    public function failureBackUrl(): string;

    public function pendingBackUrl(): string;

    public function expiration(): DateTime | null;
}
