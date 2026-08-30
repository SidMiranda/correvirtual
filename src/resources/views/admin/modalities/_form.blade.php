@php $modality = $modality ?? null; @endphp

<div class="form-group">
    <label class="small mb-1" for="name">Nome da modalidade</label>
    <input class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" type="text" required maxlength="255"
           placeholder="Ex.: 10km, Caminhada 3km, Desafio 50km"
           value="{{ old('name', $modality?->name) }}">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="distance_km">Distância em km</label>
            <input class="form-control @error('distance_km') is-invalid @enderror"
                   id="distance_km" name="distance_km" type="number" step="0.01" min="0"
                   placeholder="Ex.: 10"
                   value="{{ old('distance_km', $modality?->distance_km) }}">
            @error('distance_km')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="form-text text-muted">Opcional. Serve para ordenar as modalidades na tela.</small>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="max_participants">Limite de vagas</label>
            <input class="form-control @error('max_participants') is-invalid @enderror"
                   id="max_participants" name="max_participants" type="number" min="1"
                   placeholder="Deixe em branco para não limitar"
                   value="{{ old('max_participants', $modality?->max_participants) }}">
            @error('max_participants')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="form-text text-muted">O sistema ainda não bloqueia inscrição ao atingir o limite — hoje é só informativo.</small>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="active" value="0">
        <input class="custom-control-input" id="active" name="active" type="checkbox" value="1"
               {{ old('active', $modality?->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Modalidade ativa</label>
    </div>
    <small class="form-text text-muted">Só modalidades ativas aparecem para o atleta escolher.</small>
</div>
