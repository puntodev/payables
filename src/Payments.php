<?php

namespace Puntodev\Payables;

use Puntodev\Payables\Exceptions\InvalidGateway;

class Payments
{
    /**
     * @throws InvalidGateway if the provided gateway is not configured
     */
    public function checkout(string $gateway, PaymentOrder $order, Merchant $merchant = null)
    {
        if (!array_key_exists($gateway, config('payments.gateways'))) {
            throw new InvalidGateway($gateway);
        }

        /** @var Gateway $gateway */
        $gateway = app(config('payments.gateways')[$gateway]);

        $created = $gateway->createOrder($order, $merchant);

    }
}
