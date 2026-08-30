<?php

namespace App\Support;

use App\Models\Team;
use Illuminate\Http\UploadedFile;

/**
 * O brasão da equipe, no bucket público do R2.
 *
 * Fica sob o organizador dono da equipe, como todo o resto — assim apagar um
 * organizador é apagar um prefixo só, e um caminho fora do escopo salta aos
 * olhos na revisão.
 */
class ImagensDaEquipe
{
    public static function salvarBrasao(Team $team, UploadedFile $arquivo): void
    {
        ImagemPublica::salvar(self::caminho($team), $arquivo);
    }

    public static function apagar(Team $team): void
    {
        ImagemPublica::apagar(self::caminho($team));
    }

    /** Derivado do organizador e do id da equipe, nunca do arquivo enviado. */
    public static function caminho(Team $team): string
    {
        return "publico/organizadores/{$team->organizer_id}/equipes/{$team->id}/brasao.jpg";
    }
}
