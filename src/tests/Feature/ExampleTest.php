<?php

namespace Tests\Feature;

use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Organizer::create([
            'name' => 'Organizador de Teste',
            'domain' => 'localhost',
            'email' => 'teste@example.com',
            'slug' => 'organizador-de-teste',
            'cnpj' => '00.000.000/0001-00',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
