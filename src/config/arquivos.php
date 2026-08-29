<?php

return [

    /*
    |--------------------------------------------------------------------------
    | De onde vêm as imagens públicas
    |--------------------------------------------------------------------------
    |
    | Vazio (padrão): servidas do disco do container, de `public/images`.
    | Preenchido: servidas do CDN — o valor é o prefixo completo até (e
    | incluindo) `publico`, sem barra no fim. Exemplos:
    |
    |   ARQUIVOS_BASE_URL=https://cdn.correvirtual.com.br/publico
    |   ARQUIVOS_BASE_URL=https://pub-xxxx.r2.dev/publico
    |
    | A estrutura de pastas dos dois lados é IGUAL de propósito (ver
    | docs/specs/armazenamento-r2.md) — por isso virar a chave é só preencher
    | esta variável, sem tocar em view nenhuma.
    |
    */

    'base_url' => rtrim((string) env('ARQUIVOS_BASE_URL', ''), '/'),

];
