<?php

namespace Tests\Feature\Admin;

use App\Models\Organizer;
use App\Models\Sponsor;
use App\Models\User;
use App\Support\ImagensDoPatrocinador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * O cadastro de patrocinadores no painel.
 *
 * Mesmas garantias do cadastro de equipes: o patrocinador nasce no organizador
 * de quem está logado, e um organizador não enxerga nem mexe no do outro.
 */
class SponsorCrudTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizadorA;
    private Organizer $organizadorB;
    private User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        // Disco em memória: os testes nunca tocam o bucket real.
        Storage::fake('r2');

        Organizer::factory()->create(['domain' => 'localhost']);
        $this->organizadorA = Organizer::factory()->create();
        $this->organizadorB = Organizer::factory()->create();

        $this->adminA = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $this->organizadorA->id,
        ]);
    }

    public function test_cria_patrocinador_no_proprio_organizador(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', [
                'name' => 'Pastelaria Pastelícia',
                'site_url' => 'https://pastelicia.com.br',
                'position' => 3,
                'active' => 1,
            ])
            ->assertRedirect('/admin/patrocinadores');

        $patrocinador = Sponsor::where('name', 'Pastelaria Pastelícia')->first();

        $this->assertNotNull($patrocinador);
        $this->assertSame($this->organizadorA->id, $patrocinador->organizer_id);
        $this->assertSame(3, $patrocinador->position);
        $this->assertTrue($patrocinador->active);
    }

    public function test_organizador_do_formulario_e_ignorado(): void
    {
        // O escopo vem de quem está logado, nunca do que o formulário mandou.
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', [
                'name' => 'Tentativa',
                'organizer_id' => $this->organizadorB->id,
            ])
            ->assertRedirect('/admin/patrocinadores');

        $this->assertSame(
            $this->organizadorA->id,
            Sponsor::where('name', 'Tentativa')->first()->organizer_id
        );
    }

    public function test_site_sem_esquema_e_recusado(): void
    {
        // "mobspot.com.br" sem https vira link relativo e leva para dentro do
        // painel — parece que funcionou e não funcionou.
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', [
                'name' => 'Sem esquema',
                'site_url' => 'mobspot.com.br',
            ])
            ->assertSessionHasErrors('site_url');

        $this->assertDatabaseCount('sponsors', 0);
    }

    public function test_listagem_mostra_so_patrocinadores_do_proprio_organizador(): void
    {
        Sponsor::factory()->create(['organizer_id' => $this->organizadorA->id, 'name' => 'Do Meu']);
        Sponsor::factory()->create(['organizer_id' => $this->organizadorB->id, 'name' => 'Do Vizinho']);

        $this->actingAs($this->adminA)
            ->get('/admin/patrocinadores')
            ->assertOk()
            ->assertSee('Do Meu')
            ->assertDontSee('Do Vizinho');
    }

    public function test_nao_altera_patrocinador_de_outro_organizador(): void
    {
        $doVizinho = Sponsor::factory()->create([
            'organizer_id' => $this->organizadorB->id,
            'name' => 'Do Vizinho',
        ]);

        // 404 e não 403: dizer "existe, mas não é seu" já conta algo sobre o vizinho.
        $this->actingAs($this->adminA)
            ->put("/admin/patrocinadores/{$doVizinho->id}", ['name' => 'Sequestrado'])
            ->assertNotFound();

        $this->assertSame('Do Vizinho', $doVizinho->fresh()->name);
    }

    public function test_nao_apaga_patrocinador_de_outro_organizador(): void
    {
        $doVizinho = Sponsor::factory()->create(['organizer_id' => $this->organizadorB->id]);

        $this->actingAs($this->adminA)
            ->delete("/admin/patrocinadores/{$doVizinho->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('sponsors', ['id' => $doVizinho->id]);
    }

    public function test_logo_vai_para_o_caminho_do_organizador_e_do_patrocinador(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', [
                'name' => 'Com Logo',
                'logo' => UploadedFile::fake()->image('qualquer-nome-que-o-usuario-mandou.png', 400, 200),
            ])
            ->assertRedirect('/admin/patrocinadores');

        $patrocinador = Sponsor::where('name', 'Com Logo')->first();

        // O caminho sai de ids do banco, nunca do nome do arquivo enviado.
        Storage::disk('r2')->assertExists(ImagensDoPatrocinador::caminho($patrocinador));
        $this->assertTrue($patrocinador->has_logo);
    }

    public function test_patrocinador_sem_logo_nao_grava_arquivo_nem_marca(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', ['name' => 'Sem Logo'])
            ->assertRedirect('/admin/patrocinadores');

        $patrocinador = Sponsor::where('name', 'Sem Logo')->first();

        $this->assertFalse($patrocinador->has_logo);
        Storage::disk('r2')->assertMissing(ImagensDoPatrocinador::caminho($patrocinador));
    }

    public function test_arquivo_que_nao_e_imagem_e_recusado_no_logo(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/patrocinadores', [
                'name' => 'Tentativa',
                'logo' => UploadedFile::fake()->create('contrato.pdf', 12, 'application/pdf'),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseCount('sponsors', 0);
    }

    public function test_editar_sem_enviar_arquivo_nao_apaga_o_logo(): void
    {
        $patrocinador = Sponsor::factory()->comLogo()->create([
            'organizer_id' => $this->organizadorA->id,
            'name' => 'Com Logo',
        ]);

        Storage::disk('r2')->put(ImagensDoPatrocinador::caminho($patrocinador), 'conteudo');

        $this->actingAs($this->adminA)
            ->put("/admin/patrocinadores/{$patrocinador->id}", ['name' => 'Nome Novo'])
            ->assertRedirect('/admin/patrocinadores');

        $this->assertTrue($patrocinador->fresh()->has_logo);
        Storage::disk('r2')->assertExists(ImagensDoPatrocinador::caminho($patrocinador));
    }

    public function test_apagar_patrocinador_leva_o_logo_junto(): void
    {
        $patrocinador = Sponsor::factory()->comLogo()->create(['organizer_id' => $this->organizadorA->id]);
        $caminho = ImagensDoPatrocinador::caminho($patrocinador);

        Storage::disk('r2')->put($caminho, 'conteudo');

        $this->actingAs($this->adminA)
            ->delete("/admin/patrocinadores/{$patrocinador->id}")
            ->assertRedirect('/admin/patrocinadores');

        // O caminho é derivado do id: um patrocinador futuro com o mesmo id
        // herdaria este logo se ele ficasse órfão.
        Storage::disk('r2')->assertMissing($caminho);
        $this->assertDatabaseMissing('sponsors', ['id' => $patrocinador->id]);
    }
}
