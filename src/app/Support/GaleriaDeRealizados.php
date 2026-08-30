<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Collection;

/**
 * A vitrine de provas já realizadas da home.
 *
 * Ela junta duas coisas que o visitante não tem por que distinguir:
 *
 * 1. As provas que o organizador entregou ANTES desta plataforma existir. Não
 *    têm registro no banco — só a arte, listada em `config/galeria.php`.
 * 2. As provas que aconteceram AQUI e já passaram da data.
 *
 * Sem o item 2, uma prova cadastrada sumiria do site no dia seguinte à
 * realização, e o organizador teria que pedir para alguém colocar a arte na
 * config à mão. Com ele, a prova entra na vitrine sozinha.
 *
 * O que sai daqui é sempre a mesma forma — url da arte e nome para o `alt` —,
 * então a tela não precisa saber de onde cada cartaz veio.
 */
class GaleriaDeRealizados
{
    /**
     * @param  Collection<int, Event>  $eventosPassados  eventos do organizador que já aconteceram
     * @return Collection<int, array{url: string, nome: string}>
     */
    public static function montar(int $organizerId, Collection $eventosPassados): Collection
    {
        // Evento cadastrado primeiro: é o mais recente, e a config é histórico
        // mais antigo. Mantém a vitrine em ordem decrescente de data sem
        // precisar inventar data para as artes avulsas.
        $doBanco = $eventosPassados
            ->filter(fn (Event $evento) => (bool) $evento->banner_url)
            ->map(fn (Event $evento) => [
                'url' => Arquivos::cardDoEvento($evento),
                'nome' => $evento->title,
            ]);

        $daConfig = collect(config("galeria.realizados.{$organizerId}", []))
            ->map(fn (array $arte) => [
                'url' => Arquivos::arteRealizada($organizerId, $arte['arquivo']),
                'nome' => $arte['nome'],
            ]);

        return $doBanco->concat($daConfig)->values();
    }
}
