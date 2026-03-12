<?php

namespace Database\Factories;

use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        return [
            'organizer_id' => Organizer::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->city() . ' - ' . $this->faker->stateAbbr(),
            'event_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'registration_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'banner_url' => 'https://placehold.co/1200x400/1971b1/ffffff?text=Evento+Demo',
            'active' => true,
        ];
    }
}