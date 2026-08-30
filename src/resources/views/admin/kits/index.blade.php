@extends('layouts.admin')

@section('titulo', 'Kits')
@section('icone', 'package')
@section('subtitulo', $event->title)

@section('acoes')
    @unless ($event->jaAconteceu())
        <a class="btn btn-primary" href="{{ route('admin.eventos.kits.create', $event->id) }}">
            <i class="mr-1" data-feather="plus"></i> Novo kit
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
                então kits não podem mais ser alterados aqui — mexer depois da prova bagunçaria o
                histórico de quem se inscreveu.
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($kits->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-3">Este evento ainda não tem kits. O kit define o preço — sem ele, não há o que cobrar.</p>
                    @unless ($event->jaAconteceu())
                        <a class="btn btn-primary" href="{{ route('admin.eventos.kits.create', $event->id) }}">Criar o primeiro</a>
                    @endunless
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 780px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Kit</th>
                                <th class="text-right">Preço</th>
                                <th class="text-center">Estoque</th>
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
                                            <div class="small text-muted">{{ Str::limit($kit->description, 70) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-500">R$ {{ number_format($kit->price, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $kit->stock ?? 'sem controle' }}</td>
                                    <td class="text-center">{{ $kit->subscriptions()->count() }}</td>
                                    <td class="text-center">
                                        @if ($kit->active)
                                            <span class="badge badge-success-soft text-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        @unless ($event->jaAconteceu())
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.eventos.kits.edit', [$event->id, $kit->id]) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.eventos.kits.destroy', [$event->id, $kit->id]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar o kit &quot;{{ $kit->name }}&quot;?');">
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
