<?php

namespace Tests\Feature\Admin;

use App\Models\Organizer;
use App\Models\Team;
use App\Models\User;
use App\Support\ImagensDaEquipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamCrudTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizadorA;
    private Organizer $organizadorB;
    private User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        // Disco em memoria: os testes nunca tocam o bucket real.
        Storage::fake('r2');

        Organizer::factory()->create(['domain' => 'localhost']);
        $this->organizadorA = Organizer::factory()->create();
        $this->organizadorB = Organizer::factory()->create();

        $this->adminA = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $this->organizadorA->id,
        ]);
    }

    public function test_cria_equipe_no_proprio_organizador(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/equipes', [
                'name' => 'Corre Mogi',
                'description' => 'Grupo de treino de Mogi Guaçu',
                'is_public' => 1,
                'active' => 1,
            ])
            ->assertRedirect('/admin/equipes');

        $equipe = Team::where('name', 'Corre Mogi')->first();

        $this->assertNotNull($equipe);
        $this->assertSame($this->organizadorA->id, $equipe->organizer_id);
        $this->assertSame('corre-mogi', $equipe->slug);
        $this->assertTrue($equipe->is_public);
    }

    public function test_organizador_do_formulario_e_ignorado(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/equipes', [
                'name' => 'Corre Mogi',
                'organizer_id' => $this->organizadorB->id,
                'is_public' => 1,
                'active' => 1,
            ]);

        $this->assertSame(
            $this->organizadorA->id,
            Team::where('name', 'Corre Mogi')->value('organizer_id')
        );
    }

    public function test_equipe_fechada_e_gravada_como_fechada(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/equipes', [
                'name' => 'Time Convidado',
                'is_public' => 0,
                'active' => 1,
            ]);

        $this->assertFalse(Team::where('name', 'Time Convidado')->first()->is_public);
    }

    public function test_slug_igual_em_organizadores_diferentes_nao_colide(): void
    {
        // O slug é único por organizador, não global — dois organizadores podem
        // ter uma "Corre Mogi" sem um atrapalhar o outro.
        Team::factory()->create([
            'organizer_id' => $this->organizadorB->id,
            'name' => 'Corre Mogi',
            'slug' => 'corre-mogi',
        ]);

        $this->actingAs($this->adminA)
            ->post('/admin/equipes', ['name' => 'Corre Mogi', 'is_public' => 1, 'active' => 1]);

        $this->assertSame(
            'corre-mogi',
            Team::where('organizer_id', $this->organizadorA->id)->value('slug')
        );
    }

    public function test_slug_repetido_no_mesmo_organizador_ganha_sufixo(): void
    {
        Team::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'name' => 'Corre Mogi',
            'slug' => 'corre-mogi',
        ]);

        $this->actingAs($this->adminA)
            ->post('/admin/equipes', ['name' => 'Corre Mogi', 'is_public' => 1, 'active' => 1]);

        $this->assertTrue(
            Team::where('organizer_id', $this->organizadorA->id)->where('slug', 'corre-mogi-2')->exists()
        );
    }

    public function test_listagem_mostra_so_equipes_do_proprio_organizador(): void
    {
        Team::factory()->create(['organizer_id' => $this->organizadorA->id, 'name' => 'Equipe do A']);
        Team::factory()->create(['organizer_id' => $this->organizadorB->id, 'name' => 'Equipe do B']);

        $this->actingAs($this->adminA)
            ->get('/admin/equipes')
            ->assertOk()
            ->assertSee('Equipe do A')
            ->assertDontSee('Equipe do B');
    }

    public function test_nao_altera_equipe_de_outro_organizador(): void
    {
        $doOutro = Team::factory()->create([
            'organizer_id' => $this->organizadorB->id,
            'name' => 'Intocavel',
        ]);

        $this->actingAs($this->adminA)
            ->get("/admin/equipes/{$doOutro->id}/edit")
            ->assertNotFound();

        $this->actingAs($this->adminA)
            ->put("/admin/equipes/{$doOutro->id}", ['name' => 'Invadida', 'is_public' => 1, 'active' => 1])
            ->assertNotFound();

        $this->assertSame('Intocavel', $doOutro->fresh()->name);
    }

    public function test_nao_apaga_equipe_de_outro_organizador(): void
    {
        $doOutro = Team::factory()->create(['organizer_id' => $this->organizadorB->id]);

        $this->actingAs($this->adminA)
            ->delete("/admin/equipes/{$doOutro->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('teams', ['id' => $doOutro->id]);
    }

    public function test_atleta_so_enxerga_equipe_aberta_e_ativa_do_organizador_dele(): void
    {
        // O filtro que a inscrição vai usar quando o vínculo for implementado.
        // As três condições precisam valer juntas.
        $aberta = Team::factory()->create(['organizer_id' => $this->organizadorA->id]);
        Team::factory()->fechada()->create(['organizer_id' => $this->organizadorA->id]);
        Team::factory()->inativa()->create(['organizer_id' => $this->organizadorA->id]);
        Team::factory()->create(['organizer_id' => $this->organizadorB->id]);

        $escolhiveis = Team::escolhivelPeloAtleta($this->organizadorA->id)->pluck('id');

        $this->assertSame([$aberta->id], $escolhiveis->all());
    }

    /*
    |--------------------------------------------------------------------------
    | Brasão
    |--------------------------------------------------------------------------
    */

    public function test_brasao_vai_para_o_caminho_do_organizador_e_da_equipe(): void
    {
        $this->actingAs($this->adminA)->post('/admin/equipes', [
            'name' => 'Corre Mogi',
            'brasao' => UploadedFile::fake()->image('escudo-bonito.png', 400, 400),
            'is_public' => 1,
            'active' => 1,
        ]);

        $equipe = Team::where('name', 'Corre Mogi')->firstOrFail();

        // O caminho sai do organizador dono e do id da equipe — nunca do nome
        // do arquivo enviado, que é entrada do usuário.
        Storage::disk('r2')->assertExists(
            "publico/organizadores/{$this->organizadorA->id}/equipes/{$equipe->id}/brasao.jpg"
        );
        Storage::disk('r2')->assertMissing('publico/escudo-bonito.png');

        $this->assertTrue($equipe->fresh()->has_logo);
    }

    public function test_equipe_sem_brasao_nao_grava_arquivo_nem_marca(): void
    {
        $this->actingAs($this->adminA)->post('/admin/equipes', [
            'name' => 'Sem Escudo',
            'is_public' => 1,
            'active' => 1,
        ]);

        $this->assertFalse(Team::where('name', 'Sem Escudo')->first()->has_logo);
        $this->assertCount(0, Storage::disk('r2')->allFiles());
    }

    public function test_arquivo_que_nao_e_imagem_e_recusado_no_brasao(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/equipes', [
                'name' => 'Equipe PDF',
                'brasao' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
                'is_public' => 1,
                'active' => 1,
            ])
            ->assertSessionHasErrors('brasao');

        $this->assertDatabaseCount('teams', 0);
        $this->assertCount(0, Storage::disk('r2')->allFiles());
    }

    public function test_editar_sem_enviar_arquivo_nao_apaga_o_brasao(): void
    {
        $equipe = Team::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'has_logo' => true,
        ]);

        $caminho = ImagensDaEquipe::caminho($equipe);
        Storage::disk('r2')->put($caminho, 'escudo antigo');

        $this->actingAs($this->adminA)->put("/admin/equipes/{$equipe->id}", [
            'name' => 'Nome Novo',
            'is_public' => 1,
            'active' => 1,
        ]);

        Storage::disk('r2')->assertExists($caminho);
        $this->assertSame('escudo antigo', Storage::disk('r2')->get($caminho));
        $this->assertTrue($equipe->fresh()->has_logo);
    }

    public function test_apagar_equipe_leva_o_brasao_junto(): void
    {
        // O caminho é derivado do id: uma equipe futura com o mesmo id herdaria
        // o brasão desta se ele ficasse órfão no bucket.
        $equipe = Team::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'has_logo' => true,
        ]);

        $caminho = ImagensDaEquipe::caminho($equipe);
        Storage::disk('r2')->put($caminho, 'x');

        $this->actingAs($this->adminA)->delete("/admin/equipes/{$equipe->id}");

        Storage::disk('r2')->assertMissing($caminho);
    }

    public function test_listagem_mostra_iniciais_quando_a_equipe_nao_tem_brasao(): void
    {
        Team::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'name' => 'Corre Mogi',
            'has_logo' => false,
        ]);

        $this->actingAs($this->adminA)
            ->get('/admin/equipes')
            ->assertOk()
            ->assertSee('brasao-equipe--vazio', false)
            ->assertSee('CO', false);
    }
}
