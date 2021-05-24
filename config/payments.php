<?php


use Puntodev\Payables\Gateways\MercadoPago\MercadoPagoGateway;

return [

    'gateways' =>
        [
            'mercado_pago' => MercadoPagoGateway::class,
        ],

    'prefix' => 'payments',
    'middleware' => 'web',
];
