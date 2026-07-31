<header class="cv-nav" id="cv-nav">
    <div class="cv-nav__utility">
        <div class="cv-nav__utility-inner">
            <span class="cv-nav__utility-brand">{{ $organizerName }}</span>

            <ul class="cv-nav__utility-links">
                @auth
                    <li><a href="/my-subscriptions">Minhas inscrições</a></li>
                    <li>
                        <span class="cv-nav__utility-user">{{ Auth::user()->name }}</span>
                    </li>
                    <li>
                        <form method="POST" action="/logout" class="cv-nav__logout-form">
                            @csrf
                            <button type="submit">Sair</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Entrar</a></li>
                    <li><a href="{{ route('register') }}" class="cv-nav__utility-cta">Criar conta</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="cv-nav__main">
        <div class="cv-nav__main-inner">
            <a href="/" class="cv-nav__brand">{{ $organizerName }}</a>

            <button type="button" class="cv-nav__toggle" id="cv-nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="cv-nav-links">
                <span></span><span></span><span></span>
            </button>

            <nav class="cv-nav__links" id="cv-nav-links">
                <a href="#eventos">Eventos</a>
                <a href="#sobre">Sobre</a>
                <a href="#patrocinadores">Patrocinadores</a>
                @auth
                    <a href="/my-subscriptions" class="cv-nav__links-cta">Minhas inscrições</a>
                @else
                    <a href="{{ route('login') }}" class="cv-nav__links-cta">Entrar</a>
                @endauth
            </nav>
        </div>
    </div>
</header>
