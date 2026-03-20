<!DOCTYPE html>
<html lang="pt-BR">
<x-app.head />
<body>

<x-app.top-bar />

<x-app.response-message />

<main>

@yield('content')

</main>

@stack('scripts')

</body>
</html>
