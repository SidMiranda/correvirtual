<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Carga dos eventos reais do organizador.
 *
 * Os dados foram levantados em 2026-08-29 da seção "Eventos Ativos" de
 * correvirtual.com.br e dos formulários de inscrição de cada um (Google Forms,
 * e o site da SuperAção no caso da Nossa Senhora Aparecida).
 *
 * É idempotente: roda pelo slug, então rodar de novo atualiza em vez de
 * duplicar. Modalidade e kit só são criados se ainda não existirem — assim uma
 * segunda execução não desfaz ajuste que o organizador tenha feito pelo painel.
 */
class CargaInicialDeEventos extends Command
{
    protected $signature = 'eventos:carga-inicial
                            {--organizador=1 : ID do organizador dono dos eventos}';

    protected $description = 'Cadastra os eventos ativos do Corre Virtual levantados do site do organizador';

    /**
     * Preços e datas conforme os formulários. Onde o formulário não informava
     * (prazo de inscrição de alguns), foi usada uma data razoável antes do
     * evento — está marcado com "prazo estimado" na descrição.
     */
    private function eventos(): array
    {
        return [
            [
                'slug' => '1a-corrida-e-caminhada-de-santa-edwiges',
                'title' => '1ª Corrida e Caminhada de Santa Edwiges',
                'location' => 'Mogi Guaçu - SP',
                'event_date' => '2026-08-30 07:00',
                'registration_deadline' => '2026-08-25 23:59',
                'description' => 'Corrida e caminhada em Mogi Guaçu, organização do Corre Virtual. '
                    . 'As inscrições para esta edição já foram encerradas.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada 3K', 3]],
                // Formulário já estava encerrado quando os dados foram levantados,
                // então valores e kits não puderam ser lidos. Preencher pelo painel.
                'kits' => [],
            ],
            [
                'slug' => '2a-corrida-pela-vida',
                'title' => '2ª Corrida pela Vida',
                'location' => 'Distrito de Martinho Prado - SP',
                'event_date' => '2026-09-13 07:30',
                'registration_deadline' => '2026-09-10 23:59',
                'description' => 'Treinão solidário realizado pelo FIVE GIRLS sob organização do Corre Virtual. '
                    . 'Concentração às 6h30 e largada às 7h30. Troféu para os 5 primeiros do geral masculino e '
                    . 'feminino, e para as 3 maiores equipes. A inscrição inclui uma doação (1L de leite ou 1kg de '
                    . 'alimento), entregue na retirada do número de peito. Camisetas vendidas à parte. '
                    . 'Prazo de inscrição estimado.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada 3K', 3], ['Corridinha Kids', null]],
                'kits' => [
                    ['Inscrição sem camiseta', 39.90, 'Número de peito e participação. Não inclui camiseta. Acompanha doação de 1L de leite ou 1kg de alimento.'],
                ],
            ],
            [
                'slug' => 'desafio-virtual-eu-corro-pra-comer-pastel-2a-edicao',
                'title' => 'Desafio Virtual — Eu Corro pra Comer Pastel (2ª edição)',
                'location' => 'Desafio virtual — encerramento na Pastelaria Pastelícia, Campo da Brahma, Mogi Guaçu - SP',
                'event_date' => '2026-10-31 23:59',
                'registration_deadline' => '2026-09-30 23:59',
                'description' => 'Corra ou caminhe de 01/10 a 31/10. Não existe distância mínima — apenas dê o seu '
                    . 'máximo. As 5 maiores distâncias no masculino e no feminino da corrida recebem troféu, e todos '
                    . 'os inscritos recebem medalha de ferro exclusiva. Uso do Strava obrigatório para a corrida; '
                    . 'não vale esteira, não são somadas atividades abaixo de 1km, e é considerada corrida apenas '
                    . 'com pace médio até 8min/km. Confraternização de encerramento com pastel grátis em novembro, '
                    . 'data a definir. Camiseta vendida separadamente.',
                'modalidades' => [['Corrida', null], ['Caminhada', null]],
                'kits' => [
                    ['Inscrição no desafio', 49.90, 'Medalha de ferro exclusiva e participação no ranking. Camiseta vendida separadamente.'],
                ],
            ],
            [
                'slug' => '1a-corrida-de-nossa-senhora-aparecida',
                'title' => '1ª Corrida de Nossa Senhora Aparecida',
                'location' => 'Paróquia Nossa Senhora da Conceição Aparecida — Rua José Ferreira de Campos, 270/306, Jd. Novo I, Mogi Guaçu - SP',
                'event_date' => '2026-10-11 07:30',
                'registration_deadline' => '2026-10-08 23:59',
                'description' => 'Idealização e realização da Paróquia Nossa Senhora da Conceição Aparecida, com '
                    . 'organização do Corre Virtual. Largada às 7h30. Retirada de kit em 10/10 das 14h às 20h e em '
                    . '11/10 das 5h30 às 6h30. Premiação do 1º ao 5º colocado no geral e nas categorias, e para as '
                    . '3 maiores equipes. Troféu e medalha personalizados em ferro fundido, camiseta e seguro de '
                    . 'vida inclusos. Prazo de inscrição estimado.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada Livre', null], ['Corrida Kids', null]],
                'kits' => [
                    ['Inscrição', 89.90, 'Camiseta, troféu e medalha em ferro fundido, seguro de vida.'],
                    ['Inscrição 60+', 69.90, 'Mesmo kit, valor reduzido para participantes a partir de 60 anos.'],
                    ['Corrida Kids', 59.90, 'Inscrição infantil.'],
                ],
            ],
            [
                'slug' => '2o-corra-que-a-bruxa-vem-ai',
                'title' => '2º Corra que a Bruxa Vem Aí!',
                'location' => 'Horto Florestal — Mogi Guaçu - SP',
                'event_date' => '2026-10-31 19:00',
                'registration_deadline' => '2026-10-15 23:59',
                'description' => 'Treinão noturno de corrida e caminhada com organização do Corre Virtual. '
                    . 'Concentração às 18h e largada às 19h. Medalha de ferro exclusiva para todos os concluintes, '
                    . 'troféu para os 10 primeiros colocados masculino e feminino no geral, e troféu para a equipe '
                    . 'com maior número de atletas inscritos.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada 3K', 3], ['Corridinha Kids', null]],
                'kits' => [
                    ['Inscrição sem camiseta', 59.90, 'Inclui lanterna de cabeça e medalha de ferro exclusiva. Não inclui camiseta.'],
                ],
            ],
            [
                'slug' => 'vibe-run-faculdade-santa-lucia',
                'title' => 'Vibe Run — Faculdade Santa Lúcia',
                'location' => 'ParkShopping Mogi Mirim — Rua João Mantovani, 373, Lot. Santa Ana, Mogi Mirim - SP',
                'event_date' => '2026-11-01 07:00',
                'registration_deadline' => '2026-10-27 23:59',
                'description' => 'Treinão realizado pela Faculdade Santa Lúcia como projeto de extensão, com '
                    . 'organização do Corre Virtual. Concentração às 6h e largada às 7h. Medalha de ferro exclusiva '
                    . 'para todos os inscritos, troféu para os 10 primeiros do geral masculino e feminino, e para as '
                    . '3 maiores equipes. Doação voluntária de 1L de leite ou 1kg de alimento no dia do evento. '
                    . 'Prazo de inscrição estimado.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada 3K', 3]],
                'kits' => [
                    ['Sem camiseta', 59.90, 'Medalha de ferro exclusiva. Doação voluntária de 1L de leite ou 1kg de alimento no dia.'],
                    ['Com camiseta', 89.90, 'Camiseta do evento e medalha de ferro exclusiva. Doação voluntária de 1L de leite ou 1kg de alimento no dia.'],
                ],
            ],
            [
                'slug' => '1a-oab-run-rosa-e-azul',
                'title' => '1ª OAB Run Rosa e Azul',
                'location' => 'Em frente ao Estádio do Camacho — R. Hugo Pancieira, Imóvel Pedregulhal, Mogi Guaçu - SP',
                'event_date' => '2026-11-22 07:00',
                'registration_deadline' => '2026-10-30 23:59',
                'description' => 'Promoção da Comissão da Mulher e da Comissão de Esportes da OAB 61ª Subsecção Mogi '
                    . 'Guaçu, com organização do Corre Virtual. Concentração às 6h e largada às 7h. Medalha de ferro '
                    . 'exclusiva para todos os concluintes, troféu para os 10 primeiros colocados masculino e '
                    . 'feminino nas categorias Geral e Advogados, e troféu para as 3 equipes com maior número de '
                    . 'inscritos. Advogados devem informar o número de inscrição na OAB.',
                'modalidades' => [['Corrida 5K', 5], ['Caminhada 2,5K', 2.5], ['Corridinha Kids', null]],
                'kits' => [
                    ['Sem camiseta — Geral', 59.90, 'Medalha de ferro exclusiva.'],
                    ['Sem camiseta — Advogado', 49.90, 'Medalha de ferro exclusiva. Exige número de inscrição na OAB.'],
                    ['Com camiseta — Geral', 89.90, 'Camiseta do evento e medalha de ferro exclusiva.'],
                    ['Com camiseta — Advogado', 79.90, 'Camiseta do evento e medalha de ferro exclusiva. Exige número de inscrição na OAB.'],
                ],
            ],
        ];
    }

    public function handle(): int
    {
        $organizerId = (int) $this->option('organizador');

        if (!Organizer::whereKey($organizerId)->exists()) {
            $this->error("Organizador {$organizerId} não existe.");

            return self::FAILURE;
        }

        foreach ($this->eventos() as $dados) {
            $evento = Event::firstOrNew([
                'slug' => $dados['slug'],
            ]);

            $novo = !$evento->exists;

            $evento->fill([
                'organizer_id' => $organizerId,
                'title' => $dados['title'],
                'description' => $dados['description'],
                'location' => $dados['location'],
                'event_date' => $dados['event_date'],
                'registration_deadline' => $dados['registration_deadline'],
                'active' => true,
            ])->save();

            $this->line(($novo ? '  criado  ' : ' atualizado ') . "#{$evento->id} {$evento->title}");

            foreach ($dados['modalidades'] as [$nome, $km]) {
                // firstOrCreate: uma segunda execução não desfaz limite de vagas
                // ou desativação que o organizador tenha ajustado pelo painel.
                $evento->modalities()->firstOrCreate(
                    ['name' => $nome],
                    ['distance_km' => $km, 'active' => true]
                );
            }

            foreach ($dados['kits'] as [$nome, $preco, $descricao]) {
                $evento->kits()->firstOrCreate(
                    ['name' => $nome],
                    ['price' => $preco, 'description' => $descricao, 'active' => true]
                );
            }

            $this->line(sprintf(
                '           %d modalidade(s), %d kit(s)%s',
                count($dados['modalidades']),
                count($dados['kits']),
                $dados['kits'] ? '' : '  <-- sem kit: ninguém consegue se inscrever até cadastrar um'
            ));
        }

        $this->newLine();
        $this->info('Carga concluída. As imagens são geradas por `php artisan eventos:gerar-imagens`.');

        return self::SUCCESS;
    }
}
