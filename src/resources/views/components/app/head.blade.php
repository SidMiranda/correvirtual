<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />

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
    @endif

    <!-- Cor do tema da barra de status do navegador no celular (opcional, mude o #ffffff para a cor da sua marca) -->
    <meta name="theme-color" content="#ffffff">

    {{-- <link href="css/styles.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{ asset('css/top-bar.css') }}">

    @if($organizerId)
        <link rel="icon" type="image/x-icon" href="{{ \App\Support\Arquivos::logoDoOrganizador($organizerId) }}" />
    @endif

    {{-- <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/font-awesome.js') }}"></script>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js" crossorigin="anonymous"></script> --}}
    <script src="{{ asset('js/feather-icons.js') }}"></script>

    <meta charset="UTF-8">

    <title>@yield('title', 'Corre Virtual')</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">

    @stack('styles')
</head>
