<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Traz a arte oficial de cada evento do site do organizador para o R2.
 *
 * A arte que o organizador já mandou fazer (com identidade do evento,
 * patrocinador, tipografia) é melhor do que qualquer imagem gerada. Este
 * comando só existe porque essas artes estavam presas no WordPress dele; o
 * `eventos:gerar-imagens` continua valendo para evento que ainda não tem arte.
 *
 * As artes são verticais (576x1024, formato de story). Servem bem no card, que
 * recorta o centro; num banner panorâmico o recorte é agressivo, então o banner
 * fica melhor com imagem própria.
 */
class ImportarArtesDoSite extends Command
{
    protected $signature = 'eventos:importar-artes {--force : Sobrescreve quem já tem imagem}';

    protected $description = 'Importa a arte oficial de cada evento do site do organizador para o R2';

    /** slug do evento => URL da arte em correvirtual.com.br (levantado em 2026-08-29) */
    private const ARTES = [
        '1a-corrida-e-caminhada-de-santa-edwiges' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/Banner-para-o-site.png',
        '1a-corrida-de-nossa-senhora-aparecida' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/post-superacao-site.jpg',
        'desafio-virtual-eu-corro-pra-comer-pastel-2a-edicao' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/Midia-Desafio-Pastelicia.png',
        '2a-corrida-pela-vida' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/Midias-Corrida-pela-Vida-01.png',
        '2o-corra-que-a-bruxa-vem-ai' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/midia01_halloween.png',
        'vibe-run-faculdade-santa-lucia' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/Midia-Vibe-Run.png',
        '1a-oab-run-rosa-e-azul' =>
            'https://www.correvirtual.com.br/wp-content/uploads/2026/07/Midia-OAB-RUN-01.png',
    ];

    public function handle(): int
    {
        $ok = 0;
        $pulados = 0;
        $falhas = 0;

        foreach (self::ARTES as $slug => $url) {
            $evento = Event::where('slug', $slug)->first();

            if (!$evento) {
                $this->warn("  sem evento para o slug {$slug} — rode eventos:carga-inicial antes.");
                $falhas++;
                continue;
            }

            if ($evento->banner_url && !$this->option('force')) {
                $this->line("  pulado   #{$evento->id} {$evento->title} (já tem imagem; use --force)");
                $pulados++;
                continue;
            }

            // A URL do site tem sufixo de tamanho (-576x1024) na versão exibida.
            // Aqui pedimos o original, e caímos na versão redimensionada se o
            // original não estiver acessível.
            $imagem = $this->baixar($url) ?? $this->baixar($this->versaoRedimensionada($url));

            if (!$imagem) {
                $this->error("  falhou   #{$evento->id} {$evento->title} — não consegui baixar a arte.");
                $falhas++;
                continue;
            }

            $base = "publico/organizadores/{$evento->organizer_id}/eventos/{$evento->id}";
            $tipo = str_ends_with($url, '.jpg') ? 'image/jpeg' : 'image/png';

            // Mesma arte nas duas posições: é a única que existe. O card recorta
            // o centro e fica bom; o banner recorta demais — ver o comentário no
            // topo desta classe.
            foreach (['banner.jpg', 'card.jpg'] as $arquivo) {
                Storage::disk('r2')->put("{$base}/{$arquivo}", $imagem, [
                    'ContentType' => $tipo,
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ]);
            }

            $evento->banner_url = 'banner.jpg';
            $evento->save();

            $this->line(sprintf('  ok       #%d %s (%s KB)', $evento->id, $evento->title, number_format(strlen($imagem) / 1024)));
            $ok++;
        }

        $this->newLine();
        $this->info("Importadas: {$ok} | puladas: {$pulados} | falhas: {$falhas}");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function baixar(string $url): ?string
    {
        $resposta = Http::timeout(60)->get($url);

        if (!$resposta->successful()) {
            return null;
        }

        $corpo = $resposta->body();

        // Página de erro do WordPress vem como HTML com status 200 às vezes.
        return strlen($corpo) > 5000 ? $corpo : null;
    }

    private function versaoRedimensionada(string $url): string
    {
        $ext = pathinfo($url, PATHINFO_EXTENSION);

        return preg_replace('/\.' . $ext . '$/', "-576x1024.{$ext}", $url);
    }
}
