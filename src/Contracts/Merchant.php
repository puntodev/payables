<?php


namespace Puntodev\Payables\Contracts;


interface Merchant
{
    public function id(): string;

    public function clientId(): string;

    public function clientSecret(): string;
}
