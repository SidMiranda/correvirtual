<!DOCTYPE html>
<html lang="pt-BR">
<x-app.head />

<link rel="stylesheet" href="{{ asset('css/home-v2.css') }}">

<body>

    <x-app.response-message />

    <x-app.nav-v2 />

    <main>
        @yield('content')
    </main>

    <x-app.scripts />
    <script src="{{ asset('js/home-v2.js') }}" defer></script>
    @stack('scripts')
</body>

</html>
