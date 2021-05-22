<?php


namespace Puntodev\Payables\Contracts;


interface GatewayPaymentOrder
{
    public function gateway(): string;

    public function id(): string;

    public function redirectLink(): string;

    public function externalId(): string;
}
