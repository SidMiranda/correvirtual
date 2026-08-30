<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Gravar e apagar imagem no bucket público do R2.
 *
 * O que é específico de cada coisa (evento, equipe) é o CAMINHO — e ele é
 * sempre derivado de ids que vêm do banco, nunca do nome do arquivo enviado.
 * Caminho montado com dado do navegador deixaria um organizador escrever na
 * pasta de outro. Ver docs/specs/armazenamento-r2.md.
 */
class ImagemPublica
{
    public const EXTENSOES = ['jpg', 'jpeg', 'png', 'webp'];
    public const TAMANHO_MAXIMO_KB = 5120;

    public static function salvar(string $caminho, UploadedFile $arquivo): void
    {
        self::salvarConteudo($caminho, file_get_contents($arquivo->getRealPath()), $arquivo->getMimeType());
    }

    /**
     * Grava conteúdo que não veio de um formulário — imagem derivada, gerada
     * pelo próprio sistema (ver ImagemOg).
     */
    public static function salvarConteudo(string $caminho, string $conteudo, string $mime): void
    {
        Storage::disk('r2')->put(
            $caminho,
            $conteudo,
            [
                'ContentType' => $mime,
                // O nome do arquivo não muda quando a imagem é trocada, então a
                // versão antiga pode ficar no cache do CDN por um tempo — é o
                // preço de ter caminho previsível. As telas contornam isso
                // acrescentando ?v={updated_at} ao exibir.
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]
        );
    }

    public static function apagar(string $caminho): void
    {
        if (Storage::disk('r2')->exists($caminho)) {
            Storage::disk('r2')->delete($caminho);
        }
    }

    /** Regra de validação compartilhada por todos os formulários com imagem. */
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
