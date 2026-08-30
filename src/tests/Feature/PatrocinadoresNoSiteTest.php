<?php

namespace Tests\Feature;

use App\Models\Organizer;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A seção de patrocinadores da home.
 *
 * Ela saiu de seis SVGs colados na view e passou a vir do cadastro do painel —
 * trocar um patrocinador deixou de exigir deploy.
 */
class PatrocinadoresNoSiteTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create(['domain' => 'localhost']);
        config(['galeria.realizados' => []]);
    }

    private function patrocinador(array $sobrescreve = []): Sponsor
    {
        return Sponsor::factory()->create(array_merge([
            'organizer_id' => $this->organizador->id,
        ], $sobrescreve));
    }

    public function test_a_secao_nao_aparece_quando_nao_ha_patrocinador(): void
    {
        // Fileira vazia embaixo de um título é pior que não ter a seção.
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="patrocinadores"', false)
            ->assertDontSee('NOSSOS <span> PATROCINADORES </span>', false);
    }

    public function test_patrocinador_ativo_aparece_com_logo_e_link(): void
    {
        $patrocinador = $this->patrocinador([
            'name' => 'Pastelaria Pastelícia',
            'site_url' => 'https://pastelicia.com.br',
            'has_logo' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="patrocinadores"', false)
            ->assertSee('https://pastelicia.com.br', false)
            ->assertSee("patrocinadores/{$patrocinador->id}/logo.png", false)
            // rel="noopener": sem isso a página aberta ganha acesso a esta.
            ->assertSee('rel="noopener"', false);
    }

    public function test_patrocinador_sem_logo_mostra_o_nome(): void
    {
        // Melhor o nome que um buraco na fileira.
        $this->patrocinador(['name' => 'Marca Sem Arte', 'has_logo' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('patrocinador__nome', false)
            ->assertSee('Marca Sem Arte');
    }

    public function test_patrocinador_inativo_fica_fora_do_site(): void
    {
        // É como o organizador tira alguém quando o contrato acaba, sem apagar.
        $this->patrocinador(['name' => 'Contrato Vencido', 'active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Contrato Vencido');
    }

    public function test_patrocinador_de_outro_organizador_nao_aparece(): void
    {
        $vizinho = Organizer::factory()->create();
        Sponsor::factory()->create(['organizer_id' => $vizinho->id, 'name' => 'Marca Do Vizinho']);

        $this->get('/')->assertOk()->assertDontSee('Marca Do Vizinho');
    }

    public function test_a_ordem_e_a_que_o_organizador_definiu(): void
    {
        // Quem aparece primeiro é negociado no contrato, não alfabético.
        $this->patrocinador(['name' => 'Aparece Depois', 'position' => 9, 'has_logo' => false]);
        $this->patrocinador(['name' => 'Aparece Antes', 'position' => 1, 'has_logo' => false]);

        $conteudo = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($conteudo, 'Aparece Depois'),
            strpos($conteudo, 'Aparece Antes')
        );
    }
}
