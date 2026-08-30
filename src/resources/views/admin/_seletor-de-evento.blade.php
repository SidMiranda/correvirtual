{{-- Cadastrar a partir do menu lateral: modalidade e kit vivem dentro de um
     evento, então antes de abrir o formulário é preciso saber qual. --}}

@if ($eventos->isEmpty())
    {{-- Só entram eventos que ainda não aconteceram: prova realizada não tem
         mais o que cadastrar. Se a lista está vazia, ou não há evento nenhum,
         ou todos já passaram. --}}
    <div class="alert alert-warning mb-0 py-2 px-3 small" role="alert">
        Nenhum evento futuro para cadastrar {{ $tipoLabel }}.
        <a href="{{ route('admin.eventos.create') }}">Criar um evento</a>.
    </div>
@else
    <form method="GET" action="{{ route('admin.catalogo.novo', $tipo) }}" class="form-inline">
        <label class="small text-muted mr-2 mb-0" for="evento">Cadastrar em</label>
        <select class="form-control mr-2" id="evento" name="evento" style="min-width: 260px;">
            @foreach ($eventos as $evento)
                <option value="{{ $evento->id }}">
                    {{ $evento->title }} ({{ $evento->event_date?->format('d/m/Y') }})
                </option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">
            <i class="mr-1" data-feather="plus"></i> {{ $botaoLabel }}
        </button>
    </form>
@endif
