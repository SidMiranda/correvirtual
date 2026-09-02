<!DOCTYPE html>
<html lang="pt-BR">
<x-app.head :og="$og ?? null" />
<x-app.top-bar />

<body>

    <x-app.response-message />

    <main>
        @yield('content')
    </main>

    <x-app.whatsapp-flutuante />

    <x-app.scripts />
    @stack('scripts')
</body>

</html>
