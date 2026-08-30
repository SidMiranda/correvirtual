<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A página de um evento.
 *
 * O topo é o mesmo para todo evento — degradê no azul do tema com o nome em
 * texto grande. A arte da prova é retrato e não cabe num quadro largo: ela é o
 * cartaz da home e é o que viaja no cartão de compartilhamento.
 */
class PaginaDoEventoTest extends TestCase
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

    public function test_o_topo_e_o_nome_sobre_o_degrade_e_nao_a_arte(): void
    {
        $evento = $this->evento();

        $resposta = $this->get('/event/' . $evento->id);

        $resposta->assertOk()
            ->assertSee('event-banner__nome', false)
            ->assertSee('Corrida da Ponte')
            ->assertSee('Mogi Guaçu - SP')
            // A arte não é mais colada no topo — nem a do evento, nem a padrão.
            ->assertDontSee('banner-img', false);
    }

    public function test_o_compartilhamento_leva_a_imagem_do_evento_e_o_texto_cadastrado(): void
    {
        $evento = $this->evento([
            'description' => 'Treinão solidário com largada às 7h30 no Campo da Brahma.',
        ]);

        $resposta = $this->get('/event/' . $evento->id);

        $resposta->assertOk()
            // A imagem do cartão é a derivada deitada, não o cartaz retrato:
            // o robô do WhatsApp recortava o meio e desistia pelo peso.
            ->assertSee("eventos/{$evento->id}/og.jpg", false)
            ->assertSee('Treinão solidário com largada às 7h30', false)
            // Dimensões declaradas: é o que faz o cartão sair grande já na
            // primeira leitura.
            ->assertSee('og:image:width', false)
            ->assertSee('content="1200"', false)
            ->assertSee('content="630"', false);
    }

    public function test_evento_sem_arte_cai_no_cartao_do_organizador(): void
    {
        // Um cartão sem imagem é bem pior que um genérico.
        $evento = $this->evento(['banner_url' => null]);

        $this->get('/event/' . $evento->id)
            ->assertOk()
            ->assertSee("organizadores/{$this->organizador->id}/og.jpg", false);
    }

    public function test_evento_de_outro_organizador_da_404(): void
    {
        $vizinho = Organizer::factory()->create();
        $evento = Event::factory()->create(['organizer_id' => $vizinho->id]);

        $this->get('/event/' . $evento->id)->assertNotFound();
    }
}
