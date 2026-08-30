<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modelo de card de evento
    |--------------------------------------------------------------------------
    |
    | 'v2'  — só a arte do evento, ocupando o card inteiro, com o botão de
    |         compartilhar por cima. Toda a informação (nome, data, local,
    |         distâncias) já está desenhada no cartaz, então repeti-la embaixo
    |         era redundante. O card inteiro é o link para a página do evento.
    |
    | 'v1'  — a arte com o degradê do evento atrás, e abaixo dela a tarja da
    |         data, o nome, o local e as distâncias em texto. É o modelo que
    |         funciona melhor para evento SEM arte, ou com arte que não traz as
    |         informações.
    |
    | Trocar é mudar CARD_DE_EVENTO no .env — as duas continuam mantidas.
    |
    */

    'card_de_evento' => env('CARD_DE_EVENTO', 'v2'),

];
