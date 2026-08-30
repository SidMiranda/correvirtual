<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use App\Support\GaleriaDeRealizados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A vitrine de provas já realizadas.
 *
 * Ela junta duas fontes que o visitante não distingue: as artes de provas
 * anteriores à plataforma (config/galeria.php) e os eventos cadastrados aqui
 * que já passaram da data. Nenhuma delas leva a lugar nenhum — a prova acabou.
 */
class GaleriaDeRealizadosTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizador = Organizer::factory()->create(['domain' => 'localhost']);

        config(['galeria.realizados' => [
            $this->organizador->id => [
                ['arquivo' => '01-prova-antiga.jpg', 'nome' => 'Prova de Antes da Plataforma'],
                ['arquivo' => '02-outra-antiga.jpg', 'nome' => 'Outra Prova Antiga'],
            ],
        ]]);
    }

    private function evento(array $sobrescreve = []): Event
    {
        return Event::factory()->create(array_merge([
            'organizer_id' => $this->organizador->id,
            'event_date' => now()->subMonth(),
            'active' => true,
        ], $sobrescreve));
    }

    public function test_a_home_mostra_as_artes_da_config(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('EVENTOS')
            ->assertSee('01-prova-antiga.jpg', false)
            ->assertSee('Prova de Antes da Plataforma');
    }

    public function test_evento_do_banco_que_ja_aconteceu_entra_na_vitrine(): void
    {
        // Sem isso, a prova cadastrada aqui sumiria do site no dia seguinte à
        // realização, e alguém teria que colocar a arte na config à mão.
        $this->evento(['title' => 'Prova Cadastrada Aqui']);

        $lista = $this->get('/')->viewData('eventosRealizados');

        $this->assertSame(
            ['Prova Cadastrada Aqui', 'Prova de Antes da Plataforma', 'Outra Prova Antiga'],
            $lista->pluck('nome')->all()
        );
    }

    public function test_evento_passado_sem_arte_fica_de_fora(): void
    {
        // A vitrine é só imagem. Um evento sem arte entraria como buraco.
        $this->evento(['title' => 'Prova Sem Arte', 'banner_url' => null]);

        $nomes = $this->get('/')->viewData('eventosRealizados')->pluck('nome')->all();

        $this->assertNotContains('Prova Sem Arte', $nomes);
    }

    public function test_os_cartazes_da_vitrine_nao_sao_link(): void
    {
        $evento = $this->evento(['title' => 'Prova Cadastrada Aqui']);

        $this->get('/')
            ->assertOk()
            ->assertSee('arte-realizada', false)
            ->assertDontSee('href="' . url('/event/' . $evento->id) . '"', false);
    }

    public function test_a_galeria_de_um_organizador_nao_vaza_no_site_do_outro(): void
    {
        $vizinho = Organizer::factory()->create();

        $lista = GaleriaDeRealizados::montar($vizinho->id, collect());

        $this->assertCount(0, $lista);
    }

    public function test_a_secao_some_quando_nao_ha_nada_para_mostrar(): void
    {
        config(['galeria.realizados' => []]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('EVENTOS <span> REALIZADOS </span>', false);
    }

    public function test_patrocinadores_ficam_entre_os_proximos_e_os_realizados(): void
    {
        $conteudo = $this->get('/')->assertOk()->getContent();

        $proximos = strpos($conteudo, 'id="eventos"');
        $patrocinadores = strpos($conteudo, 'id="patrocinadores"');
        $realizados = strpos($conteudo, 'id="eventos-realizados"');

        $this->assertNotFalse($patrocinadores);
        $this->assertGreaterThan($proximos, $patrocinadores);
        $this->assertGreaterThan($patrocinadores, $realizados);
    }
}
