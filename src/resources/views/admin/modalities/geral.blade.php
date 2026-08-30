@extends('layouts.admin')

@section('titulo', 'Modalidades')
@section('icone', 'flag')
@section('subtitulo', 'As distâncias de todos os seus eventos')

@section('acoes')
    @include('admin._seletor-de-evento', [
        'tipo' => 'modalidades',
        'tipoLabel' => 'modalidades',
        'botaoLabel' => 'Nova modalidade',
    ])
@endsection

@section('conteudo')

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($modalities->isEmpty())
                <div class="p-5 text-center text-muted">
                    <div class="mb-3"><i data-feather="flag" style="width:42px;height:42px;"></i></div>
                    <p class="mb-0">Nenhuma modalidade cadastrada ainda em nenhum evento.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 820px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Modalidade</th>
                                <th>Evento</th>
                                <th>Distância</th>
                                <th class="text-center">Inscritos</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modalities as $modalidade)
                                <tr>
                                    <td class="font-weight-500">{{ $modalidade->name }}</td>
                                    <td>
                                        <a href="{{ route('admin.eventos.modalidades.index', $modalidade->event_id) }}">
                                            {{ $modalidade->event->title }}
                                        </a>
                                        <div class="small text-muted">{{ $modalidade->event->event_date?->format('d/m/Y') }}</div>
                                    </td>
                                    <td>{{ $modalidade->distance_km ? rtrim(rtrim(number_format($modalidade->distance_km, 2, ',', '.'), '0'), ',') . ' km' : '—' }}</td>
                                    <td class="text-center">{{ $modalidade->subscriptions()->count() }}</td>
                                    <td class="text-center">
                                        @if ($modalidade->active)
                                            <span class="badge badge-success-soft text-success">Ativa</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativa</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{-- Evento já realizado não é mais editável. --}}
                                        @if ($modalidade->event->jaAconteceu())
                                            <span class="small text-muted" title="Evento já realizado">
                                                <i data-feather="lock" style="width:16px;height:16px;"></i>
                                            </span>
                                        @else
                                            <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                               href="{{ route('admin.eventos.modalidades.edit', [$modalidade->event_id, $modalidade->id]) }}" title="Editar">
                                                <i data-feather="edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($modalities->hasPages())
            <div class="card-footer">{{ $modalities->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

@endsection
