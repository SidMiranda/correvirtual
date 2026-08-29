<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Grava as imagens de um evento no R2.
 *
 * Existe separado do controller porque o caminho é derivado do organizador e do
 * evento — nunca de nada que venha do formulário. Um caminho montado com dado
 * do navegador deixaria um organizador escrever na pasta de outro.
 *
 * O disco é o R2 e não o container de propósito: o container é recriado a cada
 * deploy, e a imagem que o organizador subiu iria junto.
 *
 * Ver docs/specs/armazenamento-r2.md.
 */
class ImagensDoEvento
{
    /** Formatos aceitos no upload, e o que o R2 vai devolver como Content-Type. */
    public const EXTENSOES = ['jpg', 'jpeg', 'png', 'webp'];
    public const TAMANHO_MAXIMO_KB = 5120;

    /**
     * Salva o banner (imagem larga do topo da página do evento).
     */
    public static function salvarBanner(Event $event, UploadedFile $arquivo): void
    {
        self::salvar($event, $arquivo, 'banner');
    }

    /**
     * Salva o card (imagem da listagem).
     */
    public static function salvarCard(Event $event, UploadedFile $arquivo): void
    {
        self::salvar($event, $arquivo, 'card');
    }

    private static function salvar(Event $event, UploadedFile $arquivo, string $tipo): void
    {
        // Sempre .jpg no destino, independente do que foi enviado: o caminho é
        // derivado (organizadores/X/eventos/Y/banner.jpg) e precisa ser
        // previsível para o site montar a URL sem consultar o banco.
        $caminho = self::caminho($event, $tipo);

        Storage::disk('r2')->put(
            $caminho,
            file_get_contents($arquivo->getRealPath()),
            [
                'ContentType' => $arquivo->getMimeType(),
                // Mesmo cache das imagens que já estavam lá. Como o nome do
                // arquivo não muda quando a imagem é trocada, a versão antiga
                // pode continuar no cache do CDN por um tempo — é o preço de ter
                // caminho previsível. Ver "Consequências" no spec.
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]
        );
    }

    public static function apagar(Event $event): void
    {
        foreach (['banner', 'card'] as $tipo) {
            $caminho = self::caminho($event, $tipo);
            if (Storage::disk('r2')->exists($caminho)) {
                Storage::disk('r2')->delete($caminho);
            }
        }
    }

    /**
     * O caminho no bucket. Sai do organizador dono do evento e do id do evento,
     * nunca de entrada do usuário.
     */
    public static function caminho(Event $event, string $tipo): string
    {
        return "publico/organizadores/{$event->organizer_id}/eventos/{$event->id}/{$tipo}.jpg";
    }

    /** Regra de validação compartilhada pelos formulários. */
    public static function regraDeValidacao(): array
    {
        return [
            'nullable',
            'image',
            'mimes:' . implode(',', self::EXTENSOES),
            'max:' . self::TAMANHO_MAXIMO_KB,
        ];
    }
}
