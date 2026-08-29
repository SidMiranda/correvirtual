@extends('layouts.admin')

@section('titulo', 'Eventos')
@section('icone', 'calendar')
@section('subtitulo', 'Corridas e desafios do seu organizador')

@section('acoes')
    <a class="btn btn-primary" href="{{ route('admin.eventos.create') }}">
        <i class="mr-1" data-feather="plus"></i> Novo evento
    </a>
@endsection

@section('conteudo')

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($events->isEmpty())
                <div class="p-5 text-center text-muted">
                    <div class="mb-3"><i data-feather="calendar" style="width:42px;height:42px;"></i></div>
                    <p class="mb-3">Você ainda não cadastrou nenhum evento.</p>
                    <a class="btn btn-primary" href="{{ route('admin.eventos.create') }}">Criar o primeiro evento</a>
                </div>
            @else
                {{-- min-width força a tabela a ROLAR na horizontal em tela estreita,
                     em vez de espremer a coluna do nome do evento até quebrar uma
                     palavra por linha (visto no Playwright a 1034px). --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 920px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Evento</th>
                                <th>Data</th>
                                <th>Inscrições até</th>
                                <th class="text-center">Categorias</th>
                                <th class="text-center">Kits</th>
                                <th class="text-center">Inscritos</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $evento)
                                <tr>
                                    <td>
                                        <div class="font-weight-500">{{ $evento->title }}</div>
                                        <div class="small text-muted">{{ $evento->location }}</div>
                                    </td>
                                    <td>{{ $evento->event_date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $evento->registration_deadline->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $evento->modalities_count }}</td>
                                    <td class="text-center">{{ $evento->kits_count }}</td>
                                    <td class="text-center">{{ $evento->subscriptions_count }}</td>
                                    <td class="text-center">
                                        @if ($evento->active)
                                            <span class="badge badge-success-soft text-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.eventos.edit', $evento->id) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.eventos.destroy', $evento->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar o evento &quot;{{ $evento->title }}&quot;? Isso não pode ser desfeito.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-datatable btn-icon btn-transparent-dark" title="Apagar">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($events->hasPages())
            {{-- bootstrap-4 explícito: o paginador padrão do Laravel 12 é Tailwind,
                 que não existe neste layout (o painel é Bootstrap 4 do template). --}}
            <div class="card-footer">{{ $events->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

@endsection
