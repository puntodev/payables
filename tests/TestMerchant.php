<?php


namespace Tests;


use Puntodev\Payables\Contracts\Merchant;

class TestMerchant implements Merchant
{
    public function id(): string
    {
        return "merchant-id";
    }

    public function clientId(): string
    {
        return "some-client-id";
    }

    public function clientSecret(): string
    {
        return "some-client-secret";
    }
}
