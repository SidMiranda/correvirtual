<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventKitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'Kit Básico',
            'description' => 'Número de peito + Chip + Medalha (pós-prova)',
            'price' => 59.90,
            'stock' => 100,
            'active' => true,
        ];
    }
}