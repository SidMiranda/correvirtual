@extends('layouts.admin')

@section('titulo', 'Kits')
@section('icone', 'package')
@section('subtitulo', 'O que os atletas recebem e pagam, em todos os seus eventos')

@section('acoes')
    @include('admin._seletor-de-evento', [
        'tipo' => 'kits',
        'tipoLabel' => 'kits',
        'botaoLabel' => 'Novo kit',
    ])
@endsection

@section('conteudo')

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($kits->isEmpty())
                <div class="p-5 text-center text-muted">
                    <div class="mb-3"><i data-feather="package" style="width:42px;height:42px;"></i></div>
                    <p class="mb-0">Nenhum kit cadastrado ainda em nenhum evento.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 860px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Kit</th>
                                <th>Evento</th>
                                <th class="text-right">Preço</th>
                                <th class="text-center">Vendidos</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kits as $kit)
                                <tr>
                                    <td>
                                        <div class="font-weight-500">{{ $kit->name }}</div>
                                        @if ($kit->description)
                                            <div class="small text-muted">{{ Str::limit($kit->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.eventos.kits.index', $kit->event_id) }}">
                                            {{ $kit->event->title }}
                                        </a>
                                        <div class="small text-muted">{{ $kit->event->event_date?->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="text-right font-weight-500">R$ {{ number_format($kit->price, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $kit->subscriptions()->count() }}</td>
                                    <td class="text-center">
                                        @if ($kit->active)
                                            <span class="badge badge-success-soft text-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{-- Evento já realizado não é mais editável. --}}
                                        @if ($kit->event->jaAconteceu())
                                            <span class="small text-muted" title="Evento já realizado">
                                                <i data-feather="lock" style="width:16px;height:16px;"></i>
                                            </span>
                                        @else
                                            <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                               href="{{ route('admin.eventos.kits.edit', [$kit->event_id, $kit->id]) }}" title="Editar">
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

        @if ($kits->hasPages())
            <div class="card-footer">{{ $kits->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

@endsection
