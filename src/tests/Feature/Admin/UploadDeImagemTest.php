<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use App\Support\ImagensDoEvento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Upload das imagens do evento.
 *
 * Storage::fake('r2') troca o disco de verdade por um em memória: os testes
 * nunca tocam o bucket real.
 */
class UploadDeImagemTest extends TestCase
{
    use RefreshDatabase;

    private Organizer $organizadorA;
    private User $adminA;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');

        Organizer::factory()->create(['domain' => 'localhost']);
        $this->organizadorA = Organizer::factory()->create();
        $this->adminA = User::factory()->create([
            'role' => 'organizer_admin',
            'organizer_id' => $this->organizadorA->id,
        ]);
    }

    private function dadosValidos(array $sobrescreve = []): array
    {
        return array_merge([
            'title' => 'Corrida com Imagem',
            'description' => 'Descrição.',
            'location' => 'Mogi Guaçu - SP',
            'event_date' => now()->addMonths(2)->format('Y-m-d\TH:i'),
            'registration_deadline' => now()->addMonth()->format('Y-m-d\TH:i'),
            'active' => 1,
        ], $sobrescreve);
    }

    public function test_banner_e_card_vao_para_o_caminho_do_organizador_e_do_evento(): void
    {
        $this->actingAs($this->adminA)->post('/admin/eventos', $this->dadosValidos([
            'banner' => UploadedFile::fake()->image('meu-banner.png', 1200, 400),
            'card' => UploadedFile::fake()->image('meu-card.jpg', 600, 600),
        ]));

        $evento = Event::where('title', 'Corrida com Imagem')->firstOrFail();

        // O caminho sai do organizador dono e do id do evento — nunca do nome do
        // arquivo enviado, que é entrada do usuário.
        Storage::disk('r2')->assertExists(
            "publico/organizadores/{$this->organizadorA->id}/eventos/{$evento->id}/banner.jpg"
        );
        Storage::disk('r2')->assertExists(
            "publico/organizadores/{$this->organizadorA->id}/eventos/{$evento->id}/card.jpg"
        );

        Storage::disk('r2')->assertMissing('publico/meu-banner.png');
    }

    public function test_evento_com_imagem_passa_a_ter_banner_url_preenchido(): void
    {
        // banner_url é a marca de "tem imagem" — é ela que as views consultam,
        // já que com CDN não dá para perguntar ao disco se o arquivo existe.
        $this->actingAs($this->adminA)->post('/admin/eventos', $this->dadosValidos([
            'banner' => UploadedFile::fake()->image('b.jpg'),
        ]));

        $this->assertNotNull(Event::where('title', 'Corrida com Imagem')->value('banner_url'));
    }

    public function test_evento_sem_upload_continua_sem_imagem(): void
    {
        $this->actingAs($this->adminA)->post('/admin/eventos', $this->dadosValidos());

        $this->assertNull(Event::where('title', 'Corrida com Imagem')->value('banner_url'));
        $this->assertCount(0, Storage::disk('r2')->allFiles());
    }

    public function test_arquivo_que_nao_e_imagem_e_recusado(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/eventos', $this->dadosValidos([
                'banner' => UploadedFile::fake()->create('planilha.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('banner');

        $this->assertDatabaseCount('events', 0);
        $this->assertCount(0, Storage::disk('r2')->allFiles());
    }

    public function test_imagem_grande_demais_e_recusada(): void
    {
        $this->actingAs($this->adminA)
            ->post('/admin/eventos', $this->dadosValidos([
                'banner' => UploadedFile::fake()->image('enorme.jpg')->size(ImagensDoEvento::TAMANHO_MAXIMO_KB + 1),
            ]))
            ->assertSessionHasErrors('banner');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_editar_sem_enviar_arquivo_nao_apaga_a_imagem_existente(): void
    {
        $evento = Event::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'banner_url' => 'banner.jpg',
        ]);

        $caminho = ImagensDoEvento::caminho($evento, 'banner');
        Storage::disk('r2')->put($caminho, 'conteudo antigo');

        $this->actingAs($this->adminA)
            ->put("/admin/eventos/{$evento->id}", $this->dadosValidos(['title' => 'Nome Novo']));

        Storage::disk('r2')->assertExists($caminho);
        $this->assertSame('conteudo antigo', Storage::disk('r2')->get($caminho));
    }

    public function test_apagar_evento_leva_as_imagens_junto(): void
    {
        // O caminho é derivado do id: um evento futuro com o mesmo id herdaria
        // a imagem deste se ela ficasse órfã no bucket.
        $evento = Event::factory()->create([
            'organizer_id' => $this->organizadorA->id,
            'banner_url' => 'banner.jpg',
        ]);

        $banner = ImagensDoEvento::caminho($evento, 'banner');
        $card = ImagensDoEvento::caminho($evento, 'card');
        Storage::disk('r2')->put($banner, 'x');
        Storage::disk('r2')->put($card, 'y');

        $this->actingAs($this->adminA)->delete("/admin/eventos/{$evento->id}");

        Storage::disk('r2')->assertMissing($banner);
        Storage::disk('r2')->assertMissing($card);
    }
}
