<?php

namespace Tests;

use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

class ProductFactory extends TestbenchUserFactory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'amount' => 100,
        ];
    }
}
