<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateGeminiImages extends Command
{
    protected $signature = 'images:generate-gemini {--force : Sobrescreve arquivos já existentes}';

    protected $description = 'Gera as imagens de fundo do banner da Home v2 via Gemini (roda uma vez, offline — não é chamado em runtime)';

    private const MODEL = 'gemini-2.5-flash-image';

    private const PROMPTS = [
        'banner-1' => 'Foto realista de um grupo diverso de corredores de rua sorrindo durante uma prova, luz da manhã, cores vibrantes de azul e branco, sem texto, sem logotipos, orientação paisagem larga.',
        'banner-2' => 'Foto realista de pernas de corredores em movimento em uma corrida de rua, ângulo baixo, sensação de energia e velocidade, tons de azul, sem texto, sem logotipos, orientação paisagem larga.',
        'banner-3' => 'Foto realista de um atleta cruzando a linha de chegada com os braços erguidos comemorando, medalha no peito, luz dourada, sem texto, sem logotipos, orientação paisagem larga.',
    ];

    public function handle(): int
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            $this->error('GEMINI_API_KEY não configurada em .env (services.gemini.api_key). Nada foi gerado.');

            return self::FAILURE;
        }

        $outputDir = public_path('images/home-v2');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach (self::PROMPTS as $filename => $prompt) {
            $destination = "{$outputDir}/{$filename}.jpg";

            if (file_exists($destination) && !$this->option('force')) {
                $this->info("Já existe, pulando (use --force pra regenerar): {$filename}.jpg");

                continue;
            }

            $this->info("Gerando {$filename}.jpg...");

            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/' . self::MODEL . ':generateContent', [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                ]);

            if (!$response->successful()) {
                $this->error("Falha ao gerar {$filename}.jpg: HTTP {$response->status()} — {$response->body()}");

                continue;
            }

            $parts = $response->json('candidates.0.content.parts', []);
            $imageBase64 = null;

            foreach ($parts as $part) {
                if (isset($part['inlineData']['data'])) {
                    $imageBase64 = $part['inlineData']['data'];
                    break;
                }
            }

            if (!$imageBase64) {
                $this->error("Resposta do Gemini pra {$filename}.jpg não trouxe imagem. Corpo: {$response->body()}");

                continue;
            }

            file_put_contents($destination, base64_decode($imageBase64));
            $this->info("Salvo: public/images/home-v2/{$filename}.jpg");
        }

        $this->info('Concluído. Atualize src/resources/views/components/app/banner-v2.blade.php pra usar os novos arquivos nos slides 2 e 3.');

        return self::SUCCESS;
    }
}
