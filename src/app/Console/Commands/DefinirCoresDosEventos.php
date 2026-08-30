<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Support\ImagensDoEvento;
use Illuminate\Console\Command;

/**
 * Troca a arte dos eventos por um degradê na cor de cada um.
 *
 * A arte oficial do organizador é vertical (formato story). Encaixá-la num card
 * largo e num topo panorâmico exigia recortes agressivos, que cortavam o nome e
 * as pessoas. O degradê é desenhado pelo navegador: nunca corta, nunca desfoca,
 * e o nome do evento aparece inteiro.
 *
 * As cores foram tiradas da própria arte de cada evento — o cartaz vermelho do
 * Santa Edwiges vira um degradê puxado para vermelho escuro, e assim por diante.
 */
class DefinirCoresDosEventos extends Command
{
    protected $signature = 'eventos:definir-cores
                            {--manter-imagens : Só define as cores, sem apagar a arte do bucket}';

    protected $description = 'Define a cor de cada evento e remove as artes, para o site usar o degradê';

    /** slug => [cor, de onde ela veio na arte original] */
    private const CORES = [
        '1a-corrida-e-caminhada-de-santa-edwiges' => ['#7f1d1d', 'cartaz vermelho'],
        '2a-corrida-pela-vida' => ['#6d28d9', 'roxo do Setembro Amarelo/Vida'],
        '1a-corrida-de-nossa-senhora-aparecida' => ['#1e3a8a', 'azul do manto e do céu'],
        'desafio-virtual-eu-corro-pra-comer-pastel-2a-edicao' => ['#b45309', 'amarelo e vermelho da pastelaria'],
        '2o-corra-que-a-bruxa-vem-ai' => ['#9a3412', 'laranja de Halloween'],
        'vibe-run-faculdade-santa-lucia' => ['#047857', 'verde da faculdade'],
        '1a-oab-run-rosa-e-azul' => ['#9d174d', 'rosa da campanha'],
        // Os eventos antigos, que continuam na seção de realizados.
        'carnarun-do-quarteto-2025' => ['#be185d', 'rosa do CarnaRun'],
        '1o-corre-que-a-bruxa-vem-ai' => ['#7c2d12', 'laranja de Halloween'],
        'desafio-virtual-pastelaria-pastelicia-cia' => ['#b45309', 'amarelo da pastelaria'],
    ];

    public function handle(): int
    {
        $definidos = 0;
        $semCor = [];

        foreach (Event::orderBy('event_date')->get() as $evento) {
            [$cor, $origem] = self::CORES[$evento->slug] ?? [Event::COR_PADRAO, 'azul do tema (sem cor própria)'];

            if (!isset(self::CORES[$evento->slug])) {
                $semCor[] = $evento->title;
            }

            $evento->accent_color = $cor;

            if (!$this->option('manter-imagens') && $evento->banner_url) {
                ImagensDoEvento::apagar($evento);
                $evento->banner_url = null;
            }

            $evento->save();

            $this->line(sprintf('  %-7s #%-3d %-46s %s', $cor, $evento->id, mb_substr($evento->title, 0, 46), $origem));
            $definidos++;
        }

        $this->newLine();
        $this->info("{$definidos} evento(s) atualizados.");

        if ($semCor) {
            $this->warn('Ficaram com o azul do tema (dá para trocar pelo painel): ' . implode(', ', $semCor));
        }

        return self::SUCCESS;
    }
}
