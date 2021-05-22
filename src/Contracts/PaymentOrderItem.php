<?php


namespace Puntodev\Payables\Contracts;


interface PaymentOrderItem
{
    public function amount(): float;

    public function quantity(): int;

    public function currency(): string;

    public function description(): string;
}
