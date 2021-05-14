<?php

namespace Puntodev\Payables\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Puntodev\Payables\Models\Payment;
use Ramsey\Uuid\Uuid;

class PaymentFactory extends Factory {

    protected $model = Payment::class;

    public function definition() {
        return [
            'payment_method' => 'mercado_pago',
            'payment_reference' => strval($this->faker->numberBetween()),
            'payer_email' => $this->faker->safeEmail,
            'status' => 'paid',
            'paid_on' => $this->faker->dateTimeBetween('now', '1 week'),
            'currency' => 'ARS',
            'amount' => $this->faker->numberBetween(10, 30),
            'external_reference' => Uuid::uuid4(),
            'raw' => function (array $rawPayment) {
                return [];
            },
        ];
    }
}
