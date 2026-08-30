<?php

namespace Tests\Feature\Admin;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A porta de entrada do painel. Ver docs/specs/painel-admin.md.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // IdentifyOrganizerByDomain roda em toda request e resolve o organizador
        // pelo host default dos testes ("localhost").
        Organizer::factory()->create(['domain' => 'localhost']);
    }

    public function test_visitante_deslogado_e_mandado_para_o_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/eventos')->assertRedirect('/login');
    }

    public function test_atleta_logado_nao_entra_no_painel(): void
    {
        $atleta = User::factory()->create(['role' => 'athlete']);

        $this->actingAs($atleta)->get('/admin')->assertForbidden();
        $this->actingAs($atleta)->get('/admin/eventos')->assertForbidden();
    }

    public function test_admin_sem_organizador_nao_entra(): void
    {
        // O papel sozinho não basta: sem organizer_id todo o escopo do painel
        // ficaria sem filtro. Ver EnsureOrganizerAdmin.
        $semOrganizador = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => null,
        ]);

        $this->actingAs($semOrganizador)->get('/admin')->assertForbidden();
    }

    public function test_admin_do_organizador_entra(): void
    {
        $organizador = Organizer::factory()->create();
        $admin = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $organizador->id,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/eventos')->assertOk();
    }

    public function test_painel_responde_em_dominio_que_nao_e_de_nenhum_organizador(): void
    {
        // admin.correvirtual.com.br não pertence a organizador nenhum. Sem a
        // exceção em IdentifyOrganizerByDomain, cairia no 404 de "organizador
        // não encontrado" antes de chegar no painel.
        $organizador = Organizer::factory()->create();
        $admin = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $organizador->id,
        ]);

        $this->actingAs($admin)
            ->get('http://admin.correvirtual.com.br/admin')
            ->assertOk();
    }

    public function test_tela_de_login_abre_no_dominio_do_painel(): void
    {
        // A porta de entrada do painel é a tela de login pública, que conta com
        // $organizerId/$organizerName existindo sempre. A exceção em
        // IdentifyOrganizerByDomain devolvia cedo sem compartilhar essas
        // variáveis: o painel abria, mas o login dele dava 500. Aconteceu em
        // produção em 2026-08-29.
        $this->get('http://admin.correvirtual.com.br/login')
            ->assertOk()
            ->assertSee('Login', false);
    }

    public function test_visitante_deslogado_no_dominio_do_painel_vai_para_o_login(): void
    {
        $this->get('http://admin.correvirtual.com.br/admin')
            ->assertRedirect('/login');
    }

    public function test_dominio_desconhecido_fora_do_painel_continua_404(): void
    {
        // A exceção acima não pode ter aberto o site público para qualquer host.
        $this->get('http://dominio-que-nao-existe.example.com/')
            ->assertNotFound();
    }

    public function test_casinha_aponta_para_o_site_do_organizador_e_nao_para_o_painel(): void
    {
        // No domínio do painel, url('/') devolve a raiz dele mesmo — que o nginx
        // manda de volta para /admin. O atalho "ver o site" acabava no lugar de
        // onde saiu. O endereço tem que sair do domínio do organizador.
        $organizador = Organizer::factory()->create(['domain' => 'corrida.example.com']);
        $admin = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $organizador->id,
        ]);

        $resposta = $this->actingAs($admin)->get('http://admin.correvirtual.com.br/admin');

        $resposta->assertOk()
            ->assertSee('corrida.example.com', false)
            ->assertDontSee('href="http://admin.correvirtual.com.br/"', false);
    }
}
