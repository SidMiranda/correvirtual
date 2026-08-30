<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Os dois modelos de card da home.
 *
 * v2 mostra só o cartaz do evento (toda a informação já está desenhada nele);
 * v1 mostra a arte com data, nome, local e distâncias em texto embaixo.
 * Trocar é mudar CARD_DE_EVENTO no .env — os dois continuam mantidos, e é isso
 * que estes testes protegem.
 */
class CardDeEventoTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create(['domain' => 'localhost']);
    }

    private function evento(array $sobrescreve = []): Event
    {
        return Event::factory()->create(array_merge([
            'organizer_id' => $this->organizador->id,
            'title' => 'Corrida da Ponte',
            'location' => 'Mogi Guaçu - SP',
            'event_date' => now()->addMonth(),
            'active' => true,
        ], $sobrescreve));
    }

    public function test_v2_mostra_so_o_cartaz_e_o_botao_de_compartilhar(): void
    {
        config(['aparencia.card_de_evento' => 'v2']);
        $this->evento();

        $resposta = $this->get('/');

        $resposta->assertOk()
            ->assertSee('cartaz__compartilhar', false)
            // O texto que o v1 repetia embaixo da arte não aparece no v2.
            ->assertDontSee('event-card__date-day', false)
            ->assertDontSee('event-card__distances', false);
    }

    public function test_v1_continua_disponivel_e_mostra_os_dados_em_texto(): void
    {
        config(['aparencia.card_de_evento' => 'v1']);
        $this->evento();

        $resposta = $this->get('/');

        $resposta->assertOk()
            ->assertSee('event-card__date-day', false)
            ->assertSee('Mogi Guaçu - SP')
            ->assertDontSee('cartaz__compartilhar', false);
    }

    public function test_o_cartaz_inteiro_leva_para_a_pagina_do_evento(): void
    {
        config(['aparencia.card_de_evento' => 'v2']);
        $evento = $this->evento();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="' . url('/event/' . $evento->id) . '"', false);
    }

    public function test_evento_sem_arte_mostra_o_nome_sobre_o_degrade(): void
    {
        // Sem isso a grade ficaria com um buraco onde deveria haver um cartaz.
        config(['aparencia.card_de_evento' => 'v2']);
        $this->evento(['banner_url' => null, 'title' => 'Prova Sem Arte']);

        $this->get('/')
            ->assertOk()
            ->assertSee('cartaz__nome', false)
            ->assertSee('Prova Sem Arte');
    }

    public function test_a_cor_do_evento_entra_no_degrade(): void
    {
        config(['aparencia.card_de_evento' => 'v2']);
        $this->evento(['accent_color' => '#7f1d1d', 'banner_url' => null]);

        $this->get('/')->assertOk()->assertSee('#7f1d1d', false);
    }

    public function test_evento_sem_cor_usa_o_azul_do_tema(): void
    {
        config(['aparencia.card_de_evento' => 'v2']);
        $this->evento(['accent_color' => null, 'banner_url' => null]);

        $this->get('/')->assertOk()->assertSee(Event::COR_PADRAO, false);
    }
}
