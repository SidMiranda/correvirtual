@extends('layouts.admin')

@section('titulo', 'Patrocinadores')
@section('icone', 'award')
@section('subtitulo', 'Marcas que apoiam os seus eventos e aparecem no site')

@section('acoes')
    <a class="btn btn-primary" href="{{ route('admin.patrocinadores.create') }}">
        <i class="mr-1" data-feather="plus"></i> Novo patrocinador
    </a>
@endsection

@section('conteudo')

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($sponsors->isEmpty())
                <div class="p-5 text-center text-muted">
                    <div class="mb-3"><i data-feather="award" style="width:42px;height:42px;"></i></div>
                    <p class="mb-1">Nenhum patrocinador cadastrado ainda.</p>
                    <p class="small mb-3">Enquanto não houver nenhum, a seção de patrocinadores não aparece no site.</p>
                    <a class="btn btn-primary" href="{{ route('admin.patrocinadores.create') }}">Cadastrar o primeiro</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 760px;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Ordem</th>
                                <th>Patrocinador</th>
                                <th>Site</th>
                                <th class="text-center">Situação</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sponsors as $sponsor)
                                <tr>
                                    <td class="text-center text-muted">{{ $sponsor->position }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            {{-- O logo antes do nome. Sem logo, um lugar vazio do
                                                 mesmo tamanho — assim a coluna não desalinha e
                                                 fica claro que falta subir a arte. --}}
                                            @if ($sponsor->has_logo)
                                                <span class="logo-patrocinador mr-3">
                                                    <img src="{{ \App\Support\Arquivos::logoDoPatrocinador($sponsor) }}"
                                                         alt="{{ $sponsor->name }}"
                                                         onerror="this.closest('.logo-patrocinador').classList.add('logo-patrocinador--vazio');this.remove();">
                                                </span>
                                            @else
                                                <span class="logo-patrocinador logo-patrocinador--vazio mr-3">
                                                    <i data-feather="image"></i>
                                                </span>
                                            @endif

                                            <div>
                                                <div class="font-weight-500">{{ $sponsor->name }}</div>
                                                @if ($sponsor->description)
                                                    <div class="small text-muted">{{ Str::limit($sponsor->description, 80) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($sponsor->site_url)
                                            <a href="{{ $sponsor->site_url }}" target="_blank" rel="noopener"
                                               class="small text-primary">
                                                {{ Str::limit(preg_replace('#^https?://#', '', $sponsor->site_url), 32) }}
                                                <i data-feather="external-link" style="width:13px;height:13px;"></i>
                                            </a>
                                        @else
                                            <span class="small text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($sponsor->active)
                                            <span class="badge badge-success-soft text-success">No site</span>
                                        @else
                                            <span class="badge badge-secondary-soft text-secondary">Fora do site</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a class="btn btn-datatable btn-icon btn-transparent-dark"
                                           href="{{ route('admin.patrocinadores.edit', $sponsor->id) }}" title="Editar">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.patrocinadores.destroy', $sponsor->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Apagar o patrocinador &quot;{{ $sponsor->name }}&quot;?');">
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

        @if ($sponsors->hasPages())
            <div class="card-footer">{{ $sponsors->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>

@endsection
