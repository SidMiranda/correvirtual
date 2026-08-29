<?php

namespace Database\Factories;

use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Equipe ' . $this->faker->lastName();

        return [
            'organizer_id' => Organizer::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'description' => $this->faker->sentence(),
            'is_public' => true,
            'active' => true,
        ];
    }

    public function fechada(): static
    {
        return $this->state(fn () => ['is_public' => false]);
    }

    public function inativa(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
