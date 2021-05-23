<?php


namespace Puntodev\Payables\Contracts;


interface Merchant
{
    public function identifier(): string;

    public function type(): string;

    public function clientId(): string;

    public function clientSecret(): string;
}
