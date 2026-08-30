<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Models\Organizer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A limpeza da base de demonstração.
 *
 * O que este teste protege é a linha divisória: o comando apaga os eventos
 * mocados e todo o histórico de inscrição e pagamento, e **não encosta** nos
 * eventos reais, nas modalidades e kits deles, nem nos atletas.
 */
class LimparDadosDeTesteTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create();
    }

    private function evento(string $slug): Event
    {
        $evento = Event::factory()->create([
            'organizer_id' => $this->organizador->id,
            'slug' => $slug,
        ]);

        EventModality::factory()->create(['event_id' => $evento->id]);
        EventKit::factory()->create(['event_id' => $evento->id]);

        return $evento;
    }

    private function inscricao(Event $evento): Subscription
    {
        $inscricao = Subscription::factory()->create([
            'event_id' => $evento->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Payment::factory()->create(['subscription_id' => $inscricao->id]);

        return $inscricao;
    }

    public function test_sem_force_nao_apaga_nada(): void
    {
        $mocado = $this->evento('carnarun-do-quarteto-2025');
        $this->inscricao($mocado);

        $this->artisan('base:limpar-testes')->assertSuccessful();

        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_apaga_os_eventos_mocados_com_modalidades_e_kits(): void
    {
        $mocado = $this->evento('night-run-etapa-fogo-sp');

        $this->artisan('base:limpar-testes', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseMissing('events', ['id' => $mocado->id]);
        $this->assertDatabaseMissing('event_modalities', ['event_id' => $mocado->id]);
        $this->assertDatabaseMissing('event_kits', ['event_id' => $mocado->id]);
    }

    public function test_nao_encosta_no_evento_real_nem_nas_modalidades_e_kits_dele(): void
    {
        $real = $this->evento('1a-oab-run-rosa-e-azul');

        $this->artisan('base:limpar-testes', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('events', ['id' => $real->id]);
        $this->assertDatabaseHas('event_modalities', ['event_id' => $real->id]);
        $this->assertDatabaseHas('event_kits', ['event_id' => $real->id]);
    }

    public function test_leva_todo_o_historico_de_inscricao_e_pagamento(): void
    {
        // Inclusive a de evento que fica: o histórico inteiro é da fase de
        // demonstração, não só o dos eventos que somem.
        $real = $this->evento('2a-corrida-pela-vida');
        $this->inscricao($real);

        $this->artisan('base:limpar-testes', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('events', ['id' => $real->id]);
    }

    public function test_os_atletas_ficam(): void
    {
        $mocado = $this->evento('carnarun-do-quarteto-2025');
        $this->inscricao($mocado);

        $this->artisan('base:limpar-testes', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
    }
}
