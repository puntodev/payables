<?php

namespace Puntodev\Payables\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Puntodev\Payables\Models\Payment;
use Ramsey\Uuid\Uuid;
use Tests\Product;
use Tests\User;

class PaymentFactory extends Factory
{

    protected $model = Payment::class;

    public function definition()
    {
        $merchant = User::factory()->create();
        $product = Product::factory()->create();

        return [
            'payment_method' => 'mercado_pago',
            'payment_reference' => strval($this->faker->numberBetween()),
            'payer_email' => $this->faker->safeEmail,
            'status' => 'paid',
            'paid_on' => $this->faker->dateTimeBetween('now', '1 week'),
            'currency' => 'ARS',
            'amount' => $this->faker->numberBetween(10, 30),
            'external_reference' => Uuid::uuid4(),
            'payable_id' => $product->id,
            'payable_type' => $product->getMorphClass(),
            'merchant_id' => $merchant->id,
            'merchant_type' => $merchant->getMorphClass(),
            'raw' => function (array $rawPayment) {
                return [];
            },
        ];
    }
}
