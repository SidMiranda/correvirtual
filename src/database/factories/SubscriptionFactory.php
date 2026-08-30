<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            // modality_id e kit_id são string na tabela por herança do desenho
            // antigo (guardavam '5k_run', 'kit_basico'); hoje recebem o id da
            // linha correspondente.
            'modality_id' => '1',
            'kit_id' => '1',
            'price' => 59.90,
            'status' => 'pending',
        ];
    }
}
