<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventModalityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => '5km',
            'distance_km' => 5,
            'max_participants' => 100,
            'active' => true,
        ];
    }
}