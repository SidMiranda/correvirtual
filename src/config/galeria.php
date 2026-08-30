<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Galeria de eventos realizados
    |--------------------------------------------------------------------------
    |
    | Provas que o organizador já entregou e que nunca existiram como registro
    | neste sistema — aconteceram antes da plataforma. Elas não têm inscrição,
    | preço nem página: são vitrine, e é só a arte que interessa.
    |
    | Por isso a lista mora aqui e não numa tabela. Uma tabela com CRUD no
    | painel só se paga quando o organizador quiser mexer nisso sozinho; até
    | lá, seria estrutura sem uso. Está anotado em docs/backlog.md.
    |
    | A chave é o id do organizador — a galeria de um não pode vazar no site do
    | outro. Os arquivos ficam no bucket em
    | `publico/organizadores/{id}/realizados/{arquivo}`.
    |
    | A ordem é a que aparece na tela: da mais recente para a mais antiga, igual
    | à do site de onde as artes vieram.
    |
    */

    'realizados' => [

        1 => [
            ['arquivo' => '01-desafio-de-inverno-2026.jpg', 'nome' => 'Desafio de Inverno 2026'],
            ['arquivo' => '02-arraia-do-corre.jpg', 'nome' => '1º Arraiá do Corre — Treinão Sunset'],
            ['arquivo' => '03-sacra-run.jpg', 'nome' => '3ª Sacra Run'],
            ['arquivo' => '04-corre-pela-conscientizacao-do-autismo.jpg', 'nome' => '1º Corre pela Conscientização do Autismo'],
            ['arquivo' => '05-carnarun-do-quarteto.jpg', 'nome' => '2º CarnaRun do Quarteto'],
            ['arquivo' => '06-corre-solidario-de-natal.jpg', 'nome' => '1º Corre Solidário de Natal'],
            ['arquivo' => '07-desafio-mega-gelo-e-chopp.jpg', 'nome' => 'Desafio Virtual Mega Gelo & Chopp'],
            ['arquivo' => '08-corra-que-a-bruxa-vem-ai.jpg', 'nome' => '1º Corra que a Bruxa Vem Aí!'],
            ['arquivo' => '09-desafio-pastelicia.jpg', 'nome' => 'Desafio Virtual Pastelaria Pastelícia & Cia'],
        ],

    ],

];
