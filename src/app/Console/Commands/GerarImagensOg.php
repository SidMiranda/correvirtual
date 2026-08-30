<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Organizer;
use App\Support\ImagemOg;
use App\Support\ImagemPublica;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Gera as imagens de compartilhamento (Open Graph) do que já existe.
 *
 * A partir de agora todo evento que recebe arte no painel ganha a sua na hora
 * (ver ImagensDoEvento::gerarOg). Este comando é para o passado: os eventos
 * carregados antes disso, os banners de organizador e a imagem padrão da
 * plataforma.
 *
 * É seguro rodar de novo — só reescreve as derivadas, nunca a arte original.
 */
class GerarImagensOg extends Command
{
    protected $signature = 'og:gerar {--evento= : Gera só o evento com este id}';

    protected $description = 'Gera as imagens 1200x630 de pré-visualização de link (WhatsApp, Facebook)';

    public function handle(): int
    {
        $eventos = Event::query()
            ->whereNotNull('banner_url')
            ->when($this->option('evento'), fn ($q, $id) => $q->where('id', $id))
            ->orderBy('id')
            ->get();

        foreach ($eventos as $evento) {
            // A fonte é o card: é o cartaz que o organizador escolheu para
            // representar a prova na listagem.
            $this->gerar(
                "publico/organizadores/{$evento->organizer_id}/eventos/{$evento->id}/card.jpg",
                "publico/organizadores/{$evento->organizer_id}/eventos/{$evento->id}/og.jpg",
                "evento {$evento->id} — {$evento->title}"
            );
        }

        if ($this->option('evento')) {
            return self::SUCCESS;
        }

        foreach (Organizer::orderBy('id')->get() as $organizador) {
            $this->gerar(
                "publico/organizadores/{$organizador->id}/banner.jpg",
                "publico/organizadores/{$organizador->id}/og.jpg",
                "organizador {$organizador->id} — {$organizador->name}"
            );
        }

        $this->gerar(
            'publico/plataforma/padrao/banner.jpg',
            'publico/plataforma/padrao/og.jpg',
            'padrão da plataforma'
        );

        return self::SUCCESS;
    }

    private function gerar(string $origem, string $destino, string $rotulo): void
    {
        $disco = Storage::disk('r2');

        if (! $disco->exists($origem)) {
            $this->warn("· {$rotulo}: sem imagem de origem ({$origem}), pulei");

            return;
        }

        try {
            $jpeg = ImagemOg::gerar($disco->get($origem));
        } catch (\Throwable $e) {
            $this->error("✗ {$rotulo}: {$e->getMessage()}");

            return;
        }

        ImagemPublica::salvarConteudo($destino, $jpeg, 'image/jpeg');

        $this->info(sprintf('✓ %s (%d KB)', $rotulo, strlen($jpeg) / 1024));
    }
}
