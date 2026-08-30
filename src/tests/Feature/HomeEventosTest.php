<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A home separa o que ainda vai acontecer do que já passou.
 *
 * As duas listas têm ordens opostas de propósito: o que vem primeiro é o mais
 * próximo (é onde o atleta se inscreve); o que já passou desce pelo mais
 * recente (é histórico).
 */
class HomeEventosTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create(['domain' => 'localhost']);
    }

    private function evento(string $titulo, string $quando): Event
    {
        return Event::factory()->create([
            'organizer_id' => $this->organizador->id,
            'title' => $titulo,
            'event_date' => $quando,
            'active' => true,
        ]);
    }

    public function test_proximos_vem_do_mais_perto_para_o_mais_longe(): void
    {
        $this->evento('Prova Distante', now()->addMonths(6));
        $this->evento('Prova Logo Ali', now()->addDays(10));
        $this->evento('Prova Do Meio', now()->addMonths(2));

        $lista = $this->get('/')->viewData('proximosEventos');

        $this->assertSame(
            ['Prova Logo Ali', 'Prova Do Meio', 'Prova Distante'],
            $lista->pluck('title')->all()
        );
    }

    public function test_passados_vem_do_mais_recente_para_o_mais_antigo(): void
    {
        $this->evento('Prova Antiga', now()->subYears(2));
        $this->evento('Prova Recente', now()->subMonth());
        $this->evento('Prova Do Meio', now()->subMonths(8));

        $lista = $this->get('/')->viewData('eventosPassados');

        $this->assertSame(
            ['Prova Recente', 'Prova Do Meio', 'Prova Antiga'],
            $lista->pluck('title')->all()
        );
    }

    public function test_evento_nao_aparece_nas_duas_listas(): void
    {
        $futuro = $this->evento('Vem Ai', now()->addMonth());
        $passado = $this->evento('Ja Foi', now()->subMonth());

        $resposta = $this->get('/');

        $this->assertSame([$futuro->id], $resposta->viewData('proximosEventos')->pluck('id')->all());
        $this->assertSame([$passado->id], $resposta->viewData('eventosPassados')->pluck('id')->all());
    }

    public function test_evento_inativo_fica_fora_das_duas_listas(): void
    {
        Event::factory()->create([
            'organizer_id' => $this->organizador->id,
            'title' => 'Rascunho',
            'event_date' => now()->addMonth(),
            'active' => false,
        ]);

        $resposta = $this->get('/');

        $this->assertCount(0, $resposta->viewData('proximosEventos'));
        $this->assertCount(0, $resposta->viewData('eventosPassados'));
        $resposta->assertDontSee('Rascunho');
    }

    public function test_evento_de_outro_organizador_nao_aparece(): void
    {
        $outro = Organizer::factory()->create();
        Event::factory()->create([
            'organizer_id' => $outro->id,
            'title' => 'Prova Do Vizinho',
            'event_date' => now()->addMonth(),
        ]);

        $this->get('/')->assertDontSee('Prova Do Vizinho');
    }

    public function test_sem_evento_futuro_a_home_explica_em_vez_de_ficar_vazia(): void
    {
        $this->evento('So Passado', now()->subMonth());

        $this->get('/')
            ->assertOk()
            ->assertSee('Nenhuma prova com inscrição aberta')
            ->assertSee('EVENTOS');
    }
}
