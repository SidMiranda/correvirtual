<!DOCTYPE html>
<html lang="pt-BR">
<x-app.head />
<x-app.top-bar />

<body>

    <x-app.response-message />

    <main>
        @yield('content')
    </main>

    <x-app.scripts />
    @stack('scripts')
</body>

</html>
