@extends('layouts.admin')

@section('titulo', 'Categorias')
@section('icone', 'flag')
@section('subtitulo', $event->title)

@section('acoes')
    <a class="btn btn-primary" href="{{ route('admin.eventos.categorias.create', $event->id) }}">
        <i class="mr-1" data-feather="plus"></i> Nova categoria
    </a>
@endsection

@section('conteudo')

    @include('admin._abas-do-evento')

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($modalities->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-3">Este evento ainda não tem categorias. Sem pelo menos uma, ninguém consegue se inscrever.</p>
                    <a class="btn btn-primary" href="{{ route('admin.eventos.categorias.create', $event->id) }}">Criar a primeira</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 720px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Categoria</th>
                                <th>Distância</th>
                                <th class="text-center">Vagas</th>
                                <th class="text-center">Inscritos</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modalities as $modalidade)
                                <tr>
                                    <td class="font-weight-500">{{ $modalidade->name }}</td>
                                    <td>{{ $modalidade->distance_km ? rtrim(rtrim(number_format($modalidade->distance_km, 2, ',', '.'), '0'), ',') . ' km' : '—' }}</td>
                                    <td class="text-center">{{ $modalidade->max_participants ?? 'sem limite' }}</td>
                                    <td class="text-center">{{ $modalidade->subscriptions()->count() }}</td>
                                    <td class="text-center">
                                        @if ($modalidade->active)
                                            <span class="badge badge-success-soft text-success">Ativa</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativa</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.eventos.categorias.edit', [$event->id, $modalidade->id]) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.eventos.categorias.destroy', [$event->id, $modalidade->id]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar a categoria &quot;{{ $modalidade->name }}&quot;?');">
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
    </div>

@endsection
