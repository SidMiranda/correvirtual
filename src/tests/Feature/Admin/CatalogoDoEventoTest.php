<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Models\Organizer;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modalidades e kits de um evento.
 *
 * Elas não têm organizer_id próprio — o vínculo com o organizador passa pelo
 * evento. É exatamente por isso que o isolamento aqui merece teste separado:
 * é uma camada a mais onde o escopo pode escapar.
 */
class CatalogoDoEventoTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizadorA;
    private Organizer $organizadorB;
    private User $adminA;
    private Event $eventoDoA;
    private Event $eventoDoB;

    protected function setUp(): void
    {
        parent::setUp();

        Organizer::factory()->create(['domain' => 'localhost']);

        $this->organizadorA = Organizer::factory()->create();
        $this->organizadorB = Organizer::factory()->create();

        $this->adminA = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $this->organizadorA->id,
        ]);

        $this->eventoDoA = Event::factory()->create(['organizer_id' => $this->organizadorA->id]);
        $this->eventoDoB = Event::factory()->create(['organizer_id' => $this->organizadorB->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Modalidades
    |--------------------------------------------------------------------------
    */

    public function test_cria_modalidade_no_evento_proprio(): void
    {
        $this->actingAs($this->adminA)
            ->post("/admin/eventos/{$this->eventoDoA->id}/modalidades", [
                'name' => '10km',
                'distance_km' => 10,
                'active' => 1,
            ])
            ->assertRedirect("/admin/eventos/{$this->eventoDoA->id}/modalidades");

        $this->assertDatabaseHas('event_modalities', [
            'event_id' => $this->eventoDoA->id,
            'name' => '10km',
        ]);
    }

    public function test_nao_cria_modalidade_em_evento_de_outro_organizador(): void
    {
        $this->actingAs($this->adminA)
            ->post("/admin/eventos/{$this->eventoDoB->id}/modalidades", [
                'name' => 'Invasao',
                'active' => 1,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('event_modalities', ['name' => 'Invasao']);
    }

    public function test_nao_edita_modalidade_de_evento_de_outro_organizador(): void
    {
        $doOutro = EventModality::factory()->create([
            'event_id' => $this->eventoDoB->id,
            'name' => 'Intocavel',
        ]);

        $this->actingAs($this->adminA)
            ->get("/admin/eventos/{$this->eventoDoB->id}/modalidades/{$doOutro->id}/edit")
            ->assertNotFound();

        $this->actingAs($this->adminA)
            ->put("/admin/eventos/{$this->eventoDoB->id}/modalidades/{$doOutro->id}", [
                'name' => 'Invadida',
                'active' => 1,
            ])
            ->assertNotFound();

        $this->assertSame('Intocavel', $doOutro->fresh()->name);
    }

    public function test_modalidade_de_outro_evento_nao_e_alcancavel_pelo_proprio(): void
    {
        // O id do evento na URL é do A, mas a modalidade é de um evento do B.
        // Sem o filtro por dentro do evento, isso passaria.
        $doOutro = EventModality::factory()->create(['event_id' => $this->eventoDoB->id]);

        $this->actingAs($this->adminA)
            ->get("/admin/eventos/{$this->eventoDoA->id}/modalidades/{$doOutro->id}/edit")
            ->assertNotFound();
    }

    public function test_modalidade_com_inscritos_nao_pode_ser_apagada(): void
    {
        $modalidade = EventModality::factory()->create(['event_id' => $this->eventoDoA->id]);
        $kit = EventKit::factory()->create(['event_id' => $this->eventoDoA->id]);

        Subscription::create([
            'event_id' => $this->eventoDoA->id,
            'user_id' => User::factory()->create()->id,
            'modality_id' => $modalidade->id,
            'kit_id' => $kit->id,
            'price' => $kit->price,
            'status' => 'paid',
        ]);

        $this->actingAs($this->adminA)
            ->delete("/admin/eventos/{$this->eventoDoA->id}/modalidades/{$modalidade->id}")
            ->assertSessionHasErrors('modalidade');

        $this->assertDatabaseHas('event_modalities', ['id' => $modalidade->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Kits
    |--------------------------------------------------------------------------
    */

    public function test_cria_kit_no_evento_proprio(): void
    {
        $this->actingAs($this->adminA)
            ->post("/admin/eventos/{$this->eventoDoA->id}/kits", [
                'name' => 'Kit Camiseta',
                'price' => 79.90,
                'active' => 1,
            ])
            ->assertRedirect("/admin/eventos/{$this->eventoDoA->id}/kits");

        $this->assertDatabaseHas('event_kits', [
            'event_id' => $this->eventoDoA->id,
            'name' => 'Kit Camiseta',
            'price' => 79.90,
        ]);
    }

    public function test_kit_com_preco_zerado_e_recusado(): void
    {
        // O Mercado Pago recusa cobrança de R$ 0,00 — melhor barrar no cadastro
        // do que descobrir na hora de gerar o Pix.
        $this->actingAs($this->adminA)
            ->post("/admin/eventos/{$this->eventoDoA->id}/kits", [
                'name' => 'Kit Gratis',
                'price' => 0,
                'active' => 1,
            ])
            ->assertSessionHasErrors('price');

        $this->assertDatabaseCount('event_kits', 0);
    }

    public function test_nao_cria_kit_em_evento_de_outro_organizador(): void
    {
        $this->actingAs($this->adminA)
            ->post("/admin/eventos/{$this->eventoDoB->id}/kits", [
                'name' => 'Invasao',
                'price' => 10,
                'active' => 1,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('event_kits', ['name' => 'Invasao']);
    }

    public function test_nao_altera_kit_de_outro_organizador(): void
    {
        $doOutro = EventKit::factory()->create([
            'event_id' => $this->eventoDoB->id,
            'price' => 100,
        ]);

        $this->actingAs($this->adminA)
            ->put("/admin/eventos/{$this->eventoDoB->id}/kits/{$doOutro->id}", [
                'name' => 'Invadido',
                'price' => 0.05,
                'active' => 1,
            ])
            ->assertNotFound();

        $this->assertEquals(100, $doOutro->fresh()->price);
    }

    public function test_listagem_de_kits_so_mostra_os_do_evento(): void
    {
        EventKit::factory()->create(['event_id' => $this->eventoDoA->id, 'name' => 'Kit do A']);
        EventKit::factory()->create(['event_id' => $this->eventoDoB->id, 'name' => 'Kit do B']);

        $this->actingAs($this->adminA)
            ->get("/admin/eventos/{$this->eventoDoA->id}/kits")
            ->assertOk()
            ->assertSee('Kit do A')
            ->assertDontSee('Kit do B');
    }

    /*
    |--------------------------------------------------------------------------
    | Atalhos do menu lateral (listagem geral)
    |--------------------------------------------------------------------------
    */

    public function test_listagem_geral_de_modalidades_so_mostra_as_do_organizador(): void
    {
        EventModality::factory()->create(['event_id' => $this->eventoDoA->id, 'name' => 'Modalidade do A']);
        EventModality::factory()->create(['event_id' => $this->eventoDoB->id, 'name' => 'Modalidade do B']);

        $this->actingAs($this->adminA)
            ->get('/admin/modalidades')
            ->assertOk()
            ->assertSee('Modalidade do A')
            ->assertDontSee('Modalidade do B');
    }

    public function test_listagem_geral_de_kits_so_mostra_os_do_organizador(): void
    {
        EventKit::factory()->create(['event_id' => $this->eventoDoA->id, 'name' => 'Kit do A']);
        EventKit::factory()->create(['event_id' => $this->eventoDoB->id, 'name' => 'Kit do B']);

        $this->actingAs($this->adminA)
            ->get('/admin/kits')
            ->assertOk()
            ->assertSee('Kit do A')
            ->assertDontSee('Kit do B');
    }

    public function test_atalho_de_cadastro_leva_ao_formulario_do_evento_escolhido(): void
    {
        $this->actingAs($this->adminA)
            ->get("/admin/catalogo/modalidades/novo?evento={$this->eventoDoA->id}")
            ->assertRedirect("/admin/eventos/{$this->eventoDoA->id}/modalidades/create");
    }

    public function test_atalho_de_cadastro_recusa_evento_de_outro_organizador(): void
    {
        // O evento vem de um <select>, que e entrada do usuario como qualquer
        // outra — trocar o value no navegador nao pode abrir a porta do vizinho.
        $this->actingAs($this->adminA)
            ->get("/admin/catalogo/kits/novo?evento={$this->eventoDoB->id}")
            ->assertRedirect('/admin/kits')
            ->assertSessionHasErrors('evento');
    }
}
