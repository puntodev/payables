<?php

use Puntodev\Payables\Gateways\MercadoPagoGateway;

return [

    'gateways' =>
        [
            'mercado_pago' => MercadoPagoGateway::class,
        ],

    'prefix' => 'payments',
    'middleware' => 'web',
];
