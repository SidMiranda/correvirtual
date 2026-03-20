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
