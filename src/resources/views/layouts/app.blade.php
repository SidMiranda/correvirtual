<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Corre Virtual')</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    
    @stack('styles')
</head>
<body>
    <header class="topbar">
        <div class="topbar-content">
            @auth
                <span class="user-name">
                    👤 {{ Auth::user()->name }}
                </span>

                <a href="/my-subscriptions" class="top-link">
                    Minhas inscrições
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        Sair
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="login-btn">
                    Login
                </a>
            @endauth
        </div>
    </header>

    @if(session('success'))
        <div style="background: #1db954; color: white; padding: 15px; text-align: center; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #e74c3c; color: white; padding: 15px; text-align: center;">
            <ul style="margin: 0; padding-left: 20px; list-style-type: none;">
                @foreach($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main>
        @yield('content')
    </main>
    
    @stack('scripts')
</body>
</html>