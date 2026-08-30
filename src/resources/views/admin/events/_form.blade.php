{{-- Campos compartilhados por criar e editar. $event vem preenchido na edição
     e nulo na criação; old() sempre vence, pra não perder o que foi digitado
     quando a validação recusa o formulário. --}}

@php
    $event = $event ?? null;

    $valorData = function ($campo) use ($event) {
        $valor = old($campo, $event?->{$campo});
        if (!$valor) {
            return '';
        }
        return $valor instanceof \DateTimeInterface
            ? $valor->format('Y-m-d\TH:i')
            : \Illuminate\Support\Carbon::parse($valor)->format('Y-m-d\TH:i');
    };
@endphp

<div class="form-group">
    <label class="small mb-1" for="title">Nome do evento</label>
    <input class="form-control @error('title') is-invalid @enderror"
           id="title" name="title" type="text" required maxlength="255"
           placeholder="Ex.: 5ª Corrida de Mogi Guaçu"
           value="{{ old('title', $event?->title) }}">
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="description">Descrição</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="4" required
              placeholder="O que o atleta precisa saber sobre o evento">{{ old('description', $event?->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="location">Local</label>
    <input class="form-control @error('location') is-invalid @enderror"
           id="location" name="location" type="text" required maxlength="255"
           placeholder="Ex.: Parque dos Ingás, Mogi Guaçu — SP"
           value="{{ old('location', $event?->location) }}">
    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="event_date">Data e hora do evento</label>
            <input class="form-control @error('event_date') is-invalid @enderror"
                   id="event_date" name="event_date" type="datetime-local" required
                   value="{{ $valorData('event_date') }}">
            @error('event_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="registration_deadline">Inscrições até</label>
            <input class="form-control @error('registration_deadline') is-invalid @enderror"
                   id="registration_deadline" name="registration_deadline" type="datetime-local" required
                   value="{{ $valorData('registration_deadline') }}">
            @error('registration_deadline')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="form-text text-muted">Precisa ser antes (ou no mesmo momento) da data do evento.</small>
            @enderror
        </div>
    </div>
</div>

<hr class="my-4">
<h6 class="text-muted mb-3" style="letter-spacing:.06em; text-transform:uppercase; font-size:12px;">Imagens do evento</h6>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="banner">Banner (topo da página do evento)</label>

            @if ($event?->banner_url)
                <div class="mb-2">
                    <img src="{{ \App\Support\Arquivos::bannerDoEvento($event) }}"
                         alt="Banner atual" class="img-fluid rounded border" style="max-height: 110px;"
                         onerror="this.style.display='none';">
                </div>
            @endif

            <input class="form-control-file @error('banner') is-invalid @enderror"
                   id="banner" name="banner" type="file" accept="image/jpeg,image/png,image/webp">
            @error('banner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
                <small class="form-text text-muted">Imagem larga. JPG, PNG ou WEBP, até 5 MB.</small>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="card">Card (imagem da listagem)</label>

            @if ($event?->banner_url)
                <div class="mb-2">
                    <img src="{{ \App\Support\Arquivos::cardDoEvento($event) }}"
                         alt="Card atual" class="img-fluid rounded border" style="max-height: 110px;"
                         onerror="this.style.display='none';">
                </div>
            @endif

            <input class="form-control-file @error('card') is-invalid @enderror"
                   id="card" name="card" type="file" accept="image/jpeg,image/png,image/webp">
            @error('card')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
                <small class="form-text text-muted">Mais quadrada, aparece na lista de eventos.</small>
            @enderror
        </div>
    </div>
</div>

<p class="small text-muted">
    As imagens vão para o armazenamento externo, não para dentro do servidor — assim não se perdem numa
    atualização do sistema. Deixe em branco para manter as que já estão lá.
</p>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="active" value="0">
        <input class="custom-control-input" id="active" name="active" type="checkbox" value="1"
               {{ old('active', $event?->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Evento ativo</label>
    </div>
    <small class="form-text text-muted">Só eventos ativos aparecem no site para os atletas.</small>
</div>
