<?php

namespace Puntodev\Payables;

use Puntodev\Payables\Contracts\Gateway;
use Puntodev\Payables\Contracts\GatewayPaymentOrder;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\PaymentOrder;
use Puntodev\Payables\Exceptions\InvalidGateway;

class Payments
{
    /**
     * @throws InvalidGateway if the provided gateway is not configured
     */
    public function checkout(string $gateway, PaymentOrder $order, Merchant $merchant = null): GatewayPaymentOrder
    {
        if (!array_key_exists($gateway, config('payments.gateways'))) {
            throw new InvalidGateway($gateway);
        }

        /** @var Gateway $gateway */
        $gateway = app(config('payments.gateways')[$gateway]);

        return $gateway->createOrder($order, $merchant);
    }
}
