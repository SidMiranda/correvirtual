@extends('layouts.admin')

@section('titulo', 'Painel')
@section('icone', 'activity')
@section('subtitulo', 'Visão geral dos seus eventos e inscrições')

@section('conteudo')

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-cv-blue mb-1">Eventos</div>
                        <div class="h3 mb-0">{{ $resumo['eventos'] }}</div>
                    </div>
                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-success mb-1">Eventos ativos</div>
                        <div class="h3 mb-0">{{ $resumo['eventos_ativos'] }}</div>
                    </div>
                    <i class="fas fa-eye fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-cv-blue mb-1">Inscrições</div>
                        <div class="h3 mb-0">{{ $resumo['inscricoes'] }}</div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-success mb-1">Inscrições pagas</div>
                        <div class="h3 mb-0">{{ $resumo['inscricoes_pagas'] }}</div>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Próximos eventos</div>
        <div class="card-body p-0">
            @if ($proximos->isEmpty())
                <div class="p-4 text-muted">
                    Nenhum evento futuro cadastrado.
                    <a href="{{ route('admin.eventos.create') }}">Criar o primeiro</a>.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Evento</th>
                                <th>Data</th>
                                <th>Local</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proximos as $evento)
                                <tr>
                                    <td class="font-weight-500">{{ $evento->title }}</td>
                                    <td>{{ $evento->event_date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $evento->location }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.eventos.edit', $evento->id) }}">
                                            <i data-feather="edit"></i>
                                        </a>
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
