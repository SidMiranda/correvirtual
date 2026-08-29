@extends('layouts.admin')

@section('titulo', 'Equipes')
@section('icone', 'users')
@section('subtitulo', 'Assessorias e grupos de treino que participam dos seus eventos')

@section('acoes')
    <a class="btn btn-primary" href="{{ route('admin.equipes.create') }}">
        <i class="mr-1" data-feather="plus"></i> Nova equipe
    </a>
@endsection

@section('conteudo')

    <div class="alert alert-icon" role="alert" style="background: var(--cv-blue-pale); border: 1px solid #cfe3f2;">
        <div class="alert-icon-aside"><i data-feather="info"></i></div>
        <div class="alert-icon-content">
            O cadastro já funciona, mas a <strong>escolha da equipe pela tela de inscrição ainda não foi ligada</strong> —
            era o combinado desta rodada. Por enquanto as equipes ficam aqui, prontas para quando isso for feito.
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($teams->isEmpty())
                <div class="p-5 text-center text-muted">
                    <div class="mb-3"><i data-feather="users" style="width:42px;height:42px;"></i></div>
                    <p class="mb-3">Nenhuma equipe cadastrada ainda.</p>
                    <a class="btn btn-primary" href="{{ route('admin.equipes.create') }}">Criar a primeira</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 760px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Equipe</th>
                                <th class="text-center">Visibilidade</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teams as $team)
                                <tr>
                                    <td>
                                        <div class="font-weight-500">{{ $team->name }}</div>
                                        @if ($team->description)
                                            <div class="small text-muted">{{ Str::limit($team->description, 80) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($team->is_public)
                                            <span class="badge badge-primary-soft text-primary">Aberta</span>
                                        @else
                                            <span class="badge badge-warning-soft text-warning">Fechada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($team->active)
                                            <span class="badge badge-success-soft text-success">Ativa</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativa</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.equipes.edit', $team->id) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.equipes.destroy', $team->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar a equipe &quot;{{ $team->name }}&quot;?');">
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

        @if ($teams->hasPages())
            <div class="card-footer">{{ $teams->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

@endsection
