<?php

namespace Puntodev\Payables\Facades;

use Illuminate\Support\Facades\Facade;
use Puntodev\Payables\Payments as PaymentsFacade;

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
