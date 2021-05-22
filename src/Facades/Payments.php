<?php

namespace Puntodev\Payables\Facades;

use Illuminate\Support\Facades\Facade;
use Puntodev\Payables\Contracts\Merchant;
use Puntodev\Payables\Contracts\PaymentOrder;
use Puntodev\Payables\Payments as PaymentsFacade;

/**
 * @method static void checkout(string $gateway, PaymentOrder $paymentOrder, Merchant $merchant)
 *
 * @see \Puntodev\Payables\Payments
 */
class Payments extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PaymentsFacade::class;
    }
}
