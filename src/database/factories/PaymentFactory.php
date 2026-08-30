<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'provider' => 'mercadopago',
            'payment_method' => 'pix',
            'transaction_id' => (string) $this->faker->numberBetween(100000000000, 999999999999),
            'status' => 'pending',
        ];
    }
}
