<?php

namespace Puntodev\Payables\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Puntodev\Payables\Models\Order;
use Ramsey\Uuid\Uuid;
use Tests\Product;
use Tests\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $merchant = User::factory()->create();
        $product = Product::factory()->create();

        return [
            'payment_method' => 'mercado_pago',
            'uuid' => Uuid::uuid4(),
            'status' => 'paid',
            'currency' => 'ARS',
            'amount' => $this->faker->numberBetween(10, 30),
            'payable_id' => $product->id,
            'payable_type' => $product->getMorphClass(),
            'merchant_id' => $merchant->id,
            'merchant_type' => $merchant->getMorphClass(),
        ];
    }
}
