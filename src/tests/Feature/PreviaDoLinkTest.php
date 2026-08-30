<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O cartão de pré-visualização do link (Open Graph) — o que o WhatsApp mostra
 * quando alguém cola o endereço.
 *
 * O que estes testes protegem: a imagem precisa de URL absoluta (quem monta a
 * prévia é um servidor de fora, que não resolve caminho relativo), e a página de
 * um evento precisa falar do evento, não do organizador.
 */
class PreviaDoLinkTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create([
            'domain' => 'localhost',
            'name' => 'Corre Virtual Eventos',
        ]);
    }

    public function test_home_traz_o_cartao_com_o_nome_do_organizador(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<meta property="og:site_name" content="Corre Virtual Eventos">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_pagina_do_evento_fala_do_evento_e_nao_do_organizador(): void
    {
        $evento = Event::factory()->create([
            'organizer_id' => $this->organizador->id,
            'title' => 'Corrida da Ponte',
            'location' => 'Mogi Guaçu - SP',
            'banner_url' => 'banner.jpg',
        ]);

        $resposta = $this->get("/event/{$evento->id}");

        $resposta->assertOk()
            ->assertSee('og:title" content="Corrida da Ponte', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            // A imagem do cartão é a derivada 1200x630 do evento, não a capa
            // do organizador e não o cartaz retrato (ver ImagemOg).
            ->assertSee("eventos/{$evento->id}/og.jpg", false);
    }

    public function test_imagem_do_cartao_e_sempre_uma_url_absoluta(): void
    {
        // Sem CDN configurado o caminho seria relativo, e o servidor que monta a
        // prévia não sabe resolver isso — o cartão sairia sem imagem.
        config(['arquivos.base_url' => '']);

        $resposta = $this->get('/');

        $conteudo = $resposta->getContent();
        preg_match('/<meta property="og:image" content="([^"]+)"/', $conteudo, $achado);

        $this->assertNotEmpty($achado, 'A tag og:image não foi encontrada.');
        $this->assertStringStartsWith('http', $achado[1]);
    }

    public function test_evento_sem_imagem_cai_no_banner_padrao(): void
    {
        $evento = Event::factory()->create([
            'organizer_id' => $this->organizador->id,
            'banner_url' => null,
        ]);

        // Cartão sem imagem nenhuma é bem pior que um cartão genérico.
        $this->get("/event/{$evento->id}")
            ->assertOk()
            ->assertSee("organizadores/{$this->organizador->id}/og.jpg", false);
    }

    public function test_painel_nao_entra_em_busca_e_nao_descreve_conteudo(): void
    {
        $admin = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $this->organizador->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('Painel do organizador', false)
            ->assertSee('Área restrita', false);
    }
}
