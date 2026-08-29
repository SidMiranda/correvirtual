<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organizer>
 *
 * `EventFactory` já chamava `Organizer::factory()` antes desta classe existir —
 * nunca tinha quebrado porque os testes montavam organizador na mão.
 */
class OrganizerFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            // domain e slug são únicos no banco; o sufixo evita colisão quando
            // o faker repete um nome de empresa dentro do mesmo teste.
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'domain' => Str::slug($name) . '-' . Str::random(6) . '.example.com',
            'email' => $this->faker->unique()->companyEmail(),
            'cnpj' => $this->faker->numerify('##.###.###/0001-##'),
            'active' => true,
        ];
    }
}
