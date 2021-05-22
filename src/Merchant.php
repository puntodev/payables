<?php


namespace Puntodev\Payables;


interface Merchant
{
    public function clientId(): string;

    public function clientSecret(): string;
}
