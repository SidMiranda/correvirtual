<?php

namespace App\Support;

use App\Models\Sponsor;
use Illuminate\Http\UploadedFile;

/**
 * O logo do patrocinador, no bucket público do R2.
 *
 * Fica sob o organizador dono do patrocinador, como todo o resto — assim
 * apagar um organizador é apagar um prefixo só, e um caminho fora do escopo
 * salta aos olhos na revisão.
 */
class ImagensDoPatrocinador
{
    public static function salvarLogo(Sponsor $sponsor, UploadedFile $arquivo): void
    {
        ImagemPublica::salvar(self::caminho($sponsor), $arquivo);
    }

    public static function apagar(Sponsor $sponsor): void
    {
        ImagemPublica::apagar(self::caminho($sponsor));
    }

    /**
     * Derivado do organizador e do id do patrocinador, nunca do arquivo
     * enviado — caminho montado com dado do navegador deixaria um organizador
     * escrever na pasta de outro.
     *
     * Termina em `.png` e não em `.jpg` como os outros: logo de marca costuma
     * vir com fundo transparente, e o nome do arquivo é o que o navegador
     * mostra na aba ao abrir a imagem direto. O tipo real vai no Content-Type,
     * gravado a partir do que foi enviado.
     */
    public static function caminho(Sponsor $sponsor): string
    {
        return "publico/organizadores/{$sponsor->organizer_id}/patrocinadores/{$sponsor->id}/logo.png";
    }
}
