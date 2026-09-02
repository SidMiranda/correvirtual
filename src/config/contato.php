<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp de atendimento
    |--------------------------------------------------------------------------
    |
    | Número que atende o botão flutuante do site. Formato do wa.me: só
    | dígitos, com código do país e DDD, sem +, espaço ou traço — qualquer
    | outra coisa e o link abre o WhatsApp numa tela de "número inválido".
    |
    | Vazio desliga o botão. É o comportamento certo para um organizador que
    | ainda não informou o contato: melhor nenhum botão que um que leva a
    | lugar nenhum.
    |
    */

    'whatsapp' => env('WHATSAPP_NUMERO', '5519997061361'),

    /*
    | Texto que já vem digitado na conversa. Serve de rastreio: quem chega
    | por aqui se identifica como vindo do site, sem precisar perguntar.
    */

    'whatsapp_mensagem' => env('WHATSAPP_MENSAGEM', 'Olá, vim do site do Corre Virtual'),

];
