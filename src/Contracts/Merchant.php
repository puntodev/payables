<?php


namespace Puntodev\Payables\Contracts;


interface Merchant
{
    public function clientId(): string;

    public function clientSecret(): string;
}
