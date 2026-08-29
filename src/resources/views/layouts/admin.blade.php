<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('titulo', 'Painel') — {{ auth()->user()->organizer->name ?? 'Corre Virtual' }}</title>

    {{-- SB Admin Pro (TEMPLATES/Painel-Admin/), copiado para public/assets/admin/.
         Só o painel usa isso — o site público continua sem Bootstrap/jQuery.

         O caminho é assets/admin/ e NÃO admin/ de propósito: uma pasta física
         public/admin/ faz o nginx (try_files $uri $uri/) achar o diretório antes
         de chegar no Laravel e devolver 403 na rota /admin do painel. --}}
    <link href="{{ asset('assets/admin/css/styles.css') }}?v={{ filemtime(public_path('assets/admin/css/styles.css')) }}" rel="stylesheet">
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js" crossorigin="anonymous"></script>

    <style>
        /* Recoloração para a paleta do projeto (mesma da Home v2): o template vem
           num azul/roxo próprio que não conversa com o site público. */
        :root {
            --cv-navy: #0d1b2a;
            --cv-blue: #1a71b2;
            --cv-blue-pale: #eaf4fb;
        }
        /* Cabeçalho com a ponte de Mogi Guaçu, a mesma imagem do banner do site
           público — o painel deixa de ser um degradê genérico e passa a ter a
           cara do organizador. O degradê escuro por cima é o que mantém o texto
           branco legível sobre qualquer parte da foto. */
        .bg-gradient-primary-to-secondary {
            background:
                linear-gradient(90deg, rgba(13,27,42,.94) 0%, rgba(13,27,42,.72) 45%, rgba(26,113,178,.62) 100%),
                url('{{ \App\Support\Arquivos::imagemDaHome('banner-1-organizer-cropped.jpg') }}') center 38% / cover no-repeat,
                var(--cv-navy) !important;
        }
        .btn-primary { background-color: var(--cv-blue); border-color: var(--cv-blue); }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--cv-navy); border-color: var(--cv-navy); }
        .navbar-brand-cv { color: #fff; font-weight: 600; letter-spacing: .01em; }
        .topnav.navbar-cv { background: var(--cv-navy) !important; }
        .topnav.navbar-cv .nav-link, .topnav.navbar-cv .navbar-brand-cv { color: #fff !important; }
        .sidenav-menu .nav-link.active { color: var(--cv-blue); font-weight: 600; }
        .sidenav-menu .nav-link.active .nav-link-icon { color: var(--cv-blue); }
        .text-cv-blue { color: var(--cv-blue) !important; }
    </style>
</head>
<body class="nav-fixed">

    <nav class="topnav navbar navbar-expand shadow navbar-dark navbar-cv" id="sidenavAccordion">
        <a class="navbar-brand-cv d-none d-sm-block ml-3" href="{{ route('admin.dashboard') }}">Corre Virtual</a>
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 mr-lg-2" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>

        <ul class="navbar-nav align-items-center ml-auto">
            <li class="nav-item dropdown no-caret mr-3 dropdown-user">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
                   href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                    <h6 class="dropdown-header d-flex align-items-center">
                        <div>
                            <div class="dropdown-user-details-name">{{ auth()->user()->name }}</div>
                            <div class="dropdown-user-details-email">{{ auth()->user()->email }}</div>
                        </div>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                            Sair
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sidenav shadow-right sidenav-light">
                <div class="sidenav-menu">
                    <div class="nav accordion" id="accordionSidenav">

                        <div class="sidenav-menu-heading">Gestão</div>

                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            Painel
                        </a>

                        <a class="nav-link {{ request()->routeIs('admin.eventos.*') ? 'active' : '' }}"
                           href="{{ route('admin.eventos.index') }}">
                            <div class="nav-link-icon"><i data-feather="calendar"></i></div>
                            Eventos
                        </a>

                        <a class="nav-link {{ request()->routeIs('admin.equipes.*') ? 'active' : '' }}"
                           href="{{ route('admin.equipes.index') }}">
                            <div class="nav-link-icon"><i data-feather="users"></i></div>
                            Equipes
                        </a>

                    </div>
                </div>
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">Organizador</div>
                        <div class="sidenav-footer-title">{{ auth()->user()->organizer->name }}</div>
                    </div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                    <div class="container-fluid">
                        <div class="page-header-content pt-4">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-4">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon"><i data-feather="@yield('icone', 'file')"></i></div>
                                        @yield('titulo', 'Painel')
                                    </h1>
                                    @hasSection('subtitulo')
                                        <div class="page-header-subtitle">@yield('subtitulo')</div>
                                    @endif
                                </div>
                                @hasSection('acoes')
                                    <div class="col-12 col-xl-auto mt-4">@yield('acoes')</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </header>

                <div class="container-fluid mt-n10">

                    @if (session('sucesso'))
                        <div class="alert alert-success alert-icon" role="alert">
                            <div class="alert-icon-aside"><i class="far fa-check-circle"></i></div>
                            <div class="alert-icon-content">{{ session('sucesso') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-icon" role="alert">
                            <div class="alert-icon-aside"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="alert-icon-content">
                                @foreach ($errors->all() as $erro)
                                    <div>{{ $erro }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @yield('conteudo')
                </div>
            </main>

            <footer class="footer mt-auto footer-light">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 small">Corre Virtual — painel do organizador</div>
                        <div class="col-md-6 text-md-right small">
                            <a href="{{ url('/') }}" target="_blank">Ver o site público</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/admin/js/scripts.js') }}"></script>
    <script>if (window.feather) { feather.replace(); }</script>
    @stack('scripts')
</body>
</html>
