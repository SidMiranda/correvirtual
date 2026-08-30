@props(['og' => null])

<head>

    {{-- charset primeiro: o navegador precisa saber a codificação antes de ler
         qualquer texto, e os robôs que montam a prévia do link (WhatsApp,
         Facebook) leem só o começo do documento. Estava lá embaixo, depois de
         todas as meta tags. --}}
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>@yield('title', 'Corre Virtual')</title>

    {{-- Cartão de pré-visualização do link. Quem tem conteúdo próprio manda um
         $og do controller (ver EventsController::show); sem isso, o componente
         usa os valores do organizador. --}}
    <x-app.og
        :titulo="$og['titulo'] ?? null"
        :descricao="$og['descricao'] ?? null"
        :imagem="$og['imagem'] ?? null"
        :tipo="$og['tipo'] ?? 'website'" />

    <meta name="author" content="{{ $organizerName ?? 'Corre Virtual' }}" />

    @php
        $user = Auth::user();
    @endphp

    {{-- Sem organizador no domínio (ex.: admin.correvirtual.com.br) não há
         logo para usar como ícone — melhor nenhum que um link quebrado. --}}
    @if($organizerId)
        <!-- Ícone padrão para a aba do navegador (Favicon) -->
        <link rel="icon" type="image/jpeg" href="{{ \App\Support\Arquivos::logoDoOrganizador($organizerId) }}">

        <!-- Ícone para quando o usuário adicionar o site à tela inicial no Android e iOS -->
        <link rel="apple-touch-icon" href="{{ \App\Support\Arquivos::logoDoOrganizador($organizerId) }}">
        <link rel="icon" sizes="192x192" href="{{ \App\Support\Arquivos::logoDoOrganizador($organizerId) }}">
        <link rel="icon" type="image/x-icon" href="{{ \App\Support\Arquivos::logoDoOrganizador($organizerId) }}" />
    @endif

    <meta name="theme-color" content="#0d1b2a">

    {{-- ?v={{ filemtime(...) }}: sem isso uma correção de estilo só aparece
         para quem limpa o cache do navegador. Já mordeu três vezes neste
         projeto. --}}
    <link rel="stylesheet" href="{{ asset('css/top-bar.css') }}?v={{ filemtime(public_path('css/top-bar.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}?v={{ filemtime(public_path('css/global.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ filemtime(public_path('css/forms.css')) }}">

    {{-- <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/font-awesome.js') }}"></script>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/feather-icons.js') }}"></script>

    @stack('styles')
</head>
