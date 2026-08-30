@extends('layouts.admin')

@section('titulo', 'Modalidades')
@section('icone', 'flag')
@section('subtitulo', $event->title)

@section('acoes')
    @unless ($event->jaAconteceu())
        <a class="btn btn-primary" href="{{ route('admin.eventos.modalidades.create', $event->id) }}">
            <i class="mr-1" data-feather="plus"></i> Nova modalidade
        </a>
    @endunless
@endsection

@section('conteudo')

    @include('admin._abas-do-evento')

    @if ($event->jaAconteceu())
        <div class="alert alert-icon" role="alert" style="background:#f1f4f8; border:1px solid #dbe3ec;">
            <div class="alert-icon-aside"><i data-feather="lock"></i></div>
            <div class="alert-icon-content">
                Este evento já aconteceu em <strong>{{ $event->event_date->format('d/m/Y') }}</strong>,
                então modalidades não podem mais ser alterados aqui — mexer depois da prova bagunçaria o
                histórico de quem se inscreveu.
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($modalities->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-3">Este evento ainda não tem modalidades. Sem pelo menos uma, ninguém consegue se inscrever.</p>
                    @unless ($event->jaAconteceu())
                        <a class="btn btn-primary" href="{{ route('admin.eventos.modalidades.create', $event->id) }}">Criar a primeira</a>
                    @endunless
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 720px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Modalidade</th>
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
                                        @unless ($event->jaAconteceu())
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.eventos.modalidades.edit', [$event->id, $modalidade->id]) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.eventos.modalidades.destroy', [$event->id, $modalidade->id]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar a modalidade &quot;{{ $modalidade->name }}&quot;?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-datatable btn-icon btn-transparent-dark" title="Apagar">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                        @endunless
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
