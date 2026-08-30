{{-- Navegação entre as três telas de um mesmo evento. Fica logo abaixo do
     cabeçalho para deixar claro que modalidades e kits pertencem ao evento
     aberto, e não a uma área solta do painel. --}}

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.eventos.edit') ? 'active' : '' }}"
           href="{{ route('admin.eventos.edit', $event->id) }}">
            Dados do evento
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.eventos.modalidades.*') ? 'active' : '' }}"
           href="{{ route('admin.eventos.modalidades.index', $event->id) }}">
            Modalidades
            <span class="badge badge-secondary-soft text-secondary ml-1">{{ $event->modalities()->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.eventos.kits.*') ? 'active' : '' }}"
           href="{{ route('admin.eventos.kits.index', $event->id) }}">
            Kits
            <span class="badge badge-secondary-soft text-secondary ml-1">{{ $event->kits()->count() }}</span>
        </a>
    </li>
</ul>
