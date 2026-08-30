<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tira da base o que sobrou da fase de demonstração.
 *
 * Até 2026-08-30 nada no banco era prova real: os eventos vinham de seeder, os
 * kits eram todos de R$ 0,05 e as inscrições foram feitas por quem estava
 * testando a plataforma. Esse resto atrapalhava de verdade — aparecia em
 * "minhas inscrições" como se fosse compromisso do atleta, e apontava para
 * evento que já tinha saído do site.
 *
 * O que fica: atletas, os eventos reais do organizador, o evento de teste do
 * fluxo (que continua a R$ 0,05 de propósito) e as modalidades e kits desses.
 *
 * Simula por padrão. Só apaga com --force, e dentro de uma transação: ou some
 * tudo, ou não some nada. Feito comando e não SQL solto porque precisava rodar
 * em dois bancos (dev e produção) e porque, escrito assim, dá para revisar
 * antes e para conferir depois.
 */
class LimparDadosDeTeste extends Command
{
    protected $signature = 'base:limpar-testes {--force : Apaga de verdade (sem isto, só mostra o que sairia)}';

    protected $description = 'Remove os eventos mocados e todo o histórico de inscrição e pagamento da fase de teste';

    /**
     * Identificados por slug, não por id: os ids são diferentes em dev e em
     * produção, e um número errado aqui apagaria a prova errada.
     */
    private const EVENTOS_MOCADOS = [
        'carnarun-do-quarteto-2025',
        'primeira-corre-que-a-bruxa-vem-ai',
        'desafio-virtual-pastelaria-pastelicia-2025',
        '98-corrida-internacional-de-sao-silvestre',
        '28-maratona-internacional-de-sao-paulo',
        'night-run-etapa-fogo-sp',
    ];

    public function handle(): int
    {
        $ids = DB::table('events')->whereIn('slug', self::EVENTOS_MOCADOS)->pluck('id');

        $contas = [
            'pagamentos' => DB::table('payments')->count(),
            'inscrições' => DB::table('subscriptions')->count(),
            'kits dos eventos mocados' => DB::table('event_kits')->whereIn('event_id', $ids)->count(),
            'modalidades dos eventos mocados' => DB::table('event_modalities')->whereIn('event_id', $ids)->count(),
            'eventos mocados' => $ids->count(),
        ];

        $this->info('Banco: ' . DB::connection()->getDatabaseName());
        $this->newLine();

        foreach ($contas as $rotulo => $quantidade) {
            $this->line(sprintf('  %-34s %d', $rotulo, $quantidade));
        }

        $this->newLine();

        if (! $this->option('force')) {
            $this->warn('Simulação. Nada foi apagado — repita com --force.');

            return self::SUCCESS;
        }

        // Transação porque a ordem importa: pagamento aponta para inscrição,
        // inscrição aponta para kit, modalidade e evento. Uma falha no meio
        // deixaria referência apontando para linha que não existe mais.
        DB::transaction(function () use ($ids) {
            DB::table('payments')->delete();
            DB::table('subscriptions')->delete();
            DB::table('event_kits')->whereIn('event_id', $ids)->delete();
            DB::table('event_modalities')->whereIn('event_id', $ids)->delete();
            DB::table('events')->whereIn('id', $ids)->delete();
        });

        $this->info('Feito.');

        $this->line(sprintf(
            '  Restaram %d eventos, %d modalidades, %d kits, %d inscrições, %d pagamentos e %d usuários.',
            DB::table('events')->count(),
            DB::table('event_modalities')->count(),
            DB::table('event_kits')->count(),
            DB::table('subscriptions')->count(),
            DB::table('payments')->count(),
            DB::table('users')->count(),
        ));

        return self::SUCCESS;
    }
}
