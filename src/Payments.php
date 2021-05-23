<?php

namespace Puntodev\Payables;

use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\GatewayPaymentOrder;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\Payable;
use Puntodev\Payables\Exceptions\InvalidGateway;

class Payments
{
    /**
     * @throws InvalidGateway if the provided gateway is not configured
     */
    public function checkout(string $gateway, Payable $payable, Merchant $merchant): GatewayPaymentOrder
    {
        if (!array_key_exists($gateway, config('payments.gateways'))) {
            throw new InvalidGateway($gateway);
        }

        /** @var Gateway $gateway */
        $gateway = app(config('payments.gateways')[$gateway]);

        return $gateway->createOrder($merchant, $payable);
    }
}
