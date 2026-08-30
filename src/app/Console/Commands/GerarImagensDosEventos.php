<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Gera banner e card de cada evento via Gemini e sobe para o R2.
 *
 * Roda offline, uma vez — nunca em runtime. Chamar a API de imagem no meio de
 * uma requisição deixaria a página do evento à mercê da latência do Gemini.
 *
 * As artes reais do organizador (as que estão no site dele) são melhores que
 * qualquer coisa gerada; isto é para os eventos que ainda não têm arte, e para
 * a primeira carga não nascer com todos os cards no fallback.
 */
class GerarImagensDosEventos extends Command
{
    protected $signature = 'eventos:gerar-imagens
                            {--evento= : ID de um evento específico}
                            {--force : Regera mesmo se o evento já tiver imagem}';

    protected $description = 'Gera banner e card de cada evento via Gemini e envia para o R2';

    private const MODEL = 'gemini-2.5-flash-image';

    /**
     * O que cada evento tem de próprio na imagem. A chave é o slug; quem não
     * estiver aqui cai no prompt genérico de corrida de rua.
     */
    private const CENAS = [
        '2o-corra-que-a-bruxa-vem-ai' =>
            'corrida noturna de Halloween em um parque arborizado, corredores com lanternas de cabeça iluminando a trilha, '
            . 'abóboras decorativas ao fundo, atmosfera divertida e não assustadora, tons de laranja e roxo na noite',
        'desafio-virtual-eu-corro-pra-comer-pastel-2a-edicao' =>
            'corredor solitário correndo por uma rua arborizada de cidade pequena ao amanhecer, relógio esportivo no pulso, '
            . 'sensação de desafio pessoal e liberdade, tons quentes de amanhecer',
        '1a-oab-run-rosa-e-azul' =>
            'corrida de rua beneficente com corredores vestindo rosa e azul, faixas coloridas ao fundo, '
            . 'clima de campanha de conscientização, manhã ensolarada',
        'vibe-run-faculdade-santa-lucia' =>
            'corrida de rua com estudantes universitários jovens e animados largando em frente a um centro comercial moderno, '
            . 'início de manhã, energia e sorrisos',
        '2a-corrida-pela-vida' =>
            'corrida solidária em rua de distrito rural brasileiro, corredores de várias idades, '
            . 'caixas de doação de alimentos ao lado da largada, manhã clara',
        '1a-corrida-de-nossa-senhora-aparecida' =>
            'corrida de rua saindo de uma praça em frente a uma igreja católica de cidade do interior, '
            . 'corredores em largada, céu azul da manhã',
        '1a-corrida-e-caminhada-de-santa-edwiges' =>
            'corrida e caminhada comunitária em rua de cidade do interior, participantes de várias idades caminhando e correndo juntos, '
            . 'manhã ensolarada',
    ];

    /** O que vale para todas: o que NÃO pode aparecer. */
    private const REGRAS = 'Fotografia realista, sem nenhum texto, sem letras, sem números, sem logotipos e sem marcas visíveis. '
        . 'Pessoas brasileiras de idades e etnias variadas.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            $this->error('GEMINI_API_KEY não configurada. Nada foi gerado.');

            return self::FAILURE;
        }

        $eventos = Event::query()
            ->when($this->option('evento'), fn ($q) => $q->whereKey($this->option('evento')))
            ->orderBy('event_date')
            ->get();

        if ($eventos->isEmpty()) {
            $this->warn('Nenhum evento encontrado.');

            return self::SUCCESS;
        }

        $gerados = 0;
        $pulados = 0;
        $falhas = 0;

        foreach ($eventos as $evento) {
            if ($evento->banner_url && !$this->option('force')) {
                $this->line("  pulado   #{$evento->id} {$evento->title} (já tem imagem; use --force)");
                $pulados++;
                continue;
            }

            $cena = self::CENAS[$evento->slug]
                ?? 'corrida de rua com corredores em movimento, manhã ensolarada, clima de evento esportivo comunitário';

            $this->info("Gerando imagens de #{$evento->id} {$evento->title}...");

            $banner = $this->gerar($apiKey, "{$cena}. Enquadramento panorâmico bem largo, como um banner de topo de site. " . self::REGRAS);
            $card = $this->gerar($apiKey, "{$cena}. Enquadramento quadrado, assunto centralizado. " . self::REGRAS);

            if (!$banner || !$card) {
                $this->error("  falhou   #{$evento->id} — nenhuma imagem salva para este evento.");
                $falhas++;
                continue;
            }

            // As duas só sobem depois que as duas foram geradas: meio caminho
            // deixaria o evento com banner novo e card velho.
            Storage::disk('r2')->put(
                "publico/organizadores/{$evento->organizer_id}/eventos/{$evento->id}/banner.jpg",
                $banner,
                ['ContentType' => 'image/jpeg', 'CacheControl' => 'public, max-age=31536000, immutable']
            );

            Storage::disk('r2')->put(
                "publico/organizadores/{$evento->organizer_id}/eventos/{$evento->id}/card.jpg",
                $card,
                ['ContentType' => 'image/jpeg', 'CacheControl' => 'public, max-age=31536000, immutable']
            );

            $evento->banner_url = 'banner.jpg';
            $evento->save();

            $this->line("  ok       #{$evento->id} banner + card no R2");
            $gerados++;
        }

        $this->newLine();
        $this->info("Gerados: {$gerados} | pulados: {$pulados} | falhas: {$falhas}");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** Devolve os bytes da imagem, ou null se o Gemini não entregou uma. */
    private function gerar(string $apiKey, string $prompt): ?string
    {
        $resposta = Http::timeout(120)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/' . self::MODEL . ':generateContent', [
                'contents' => [['parts' => [['text' => $prompt]]]],
            ]);

        if (!$resposta->successful()) {
            $this->error("    HTTP {$resposta->status()}: " . mb_substr($resposta->body(), 0, 200));

            return null;
        }

        foreach ($resposta->json('candidates.0.content.parts', []) as $parte) {
            if (isset($parte['inlineData']['data'])) {
                return base64_decode($parte['inlineData']['data']);
            }
        }

        $this->error('    A resposta não trouxe imagem: ' . mb_substr($resposta->body(), 0, 200));

        return null;
    }
}
