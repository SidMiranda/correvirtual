<?php

namespace Tests\Feature;

use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O botão flutuante de WhatsApp das páginas públicas.
 *
 * O que estes testes protegem: um link de wa.me malformado não dá erro em
 * lugar nenhum — ele abre o WhatsApp numa tela de "número inválido", e a
 * pessoa que ia perguntar sobre a prova desiste. Só se descobre por
 * reclamação.
 */
class BotaoWhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Organizer::factory()->create(['domain' => 'localhost']);
        config(['galeria.realizados' => []]);
    }

    public function test_a_home_traz_o_link_do_whatsapp_com_a_mensagem_pronta(): void
    {
        config([
            'contato.whatsapp' => '5519997061361',
            'contato.whatsapp_mensagem' => 'Olá, vim do site do Corre Virtual',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://wa.me/5519997061361?text=Ol%C3%A1%2C%20vim%20do%20site%20do%20Corre%20Virtual', false)
            // Aba nova, e rel="noopener": sem ele a página aberta ganha acesso
            // a esta pela window.opener.
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener"', false);
    }

    public function test_o_numero_e_limpo_antes_de_virar_link(): void
    {
        // Quem preenche o .env escreve como escreveria numa agenda; o wa.me só
        // aceita dígitos.
        config(['contato.whatsapp' => '+55 (19) 99706-1361']);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://wa.me/5519997061361', false);
    }

    public function test_sem_numero_configurado_o_botao_nao_aparece(): void
    {
        // Melhor nenhum botão que um que leva a lugar nenhum.
        config(['contato.whatsapp' => '']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('zap-flutuante', false)
            ->assertDontSee('wa.me', false);
    }

    public function test_o_botao_tambem_aparece_nas_paginas_do_layout_antigo(): void
    {
        // "Minhas inscrições" e a página do evento usam layouts.app, não a
        // Home v2 — é exatamente onde surge dúvida que vira mensagem.
        config(['contato.whatsapp' => '5519997061361']);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get('/my-subscriptions')
            ->assertOk()
            ->assertSee('zap-flutuante', false);
    }
}
