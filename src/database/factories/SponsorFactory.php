<?php

namespace Database\Factories;

use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sponsor>
 */
class SponsorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'name' => $this->faker->company(),
            'site_url' => 'https://' . $this->faker->domainName(),
            'description' => $this->faker->sentence(),
            'has_logo' => false,
            'position' => 0,
            'active' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    public function comLogo(): static
    {
        return $this->state(fn () => ['has_logo' => true]);
    }
}
