<?php

namespace Puntodev\Payables\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Puntodev\Payables\Models\Order;
use Puntodev\Payables\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'payment_reference' => strval($this->faker->numberBetween()),
            'order_id' => fn() => Order::factory()->create()->id,
            'status' => 'paid',
            'paid_on' => $this->faker->dateTimeBetween('now', '1 week'),
            'currency' => 'ARS',
            'amount' => $this->faker->numberBetween(10, 30),
            'raw' => fn() => [],
        ];
    }
}
