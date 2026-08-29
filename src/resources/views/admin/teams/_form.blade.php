@php $team = $team ?? null; @endphp

<div class="form-group">
    <label class="small mb-1" for="name">Nome da equipe</label>
    <input class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" type="text" required maxlength="255"
           placeholder="Ex.: Corre Mogi, Assessoria Pastelícia"
           value="{{ old('name', $team?->name) }}">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="description">Descrição</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="3"
              placeholder="Opcional — quem é a equipe, onde treina">{{ old('description', $team?->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="is_public" value="0">
        <input class="custom-control-input" id="is_public" name="is_public" type="checkbox" value="1"
               {{ old('is_public', $team?->is_public ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_public">Equipe aberta</label>
    </div>
    <small class="form-text text-muted">
        <strong>Aberta:</strong> vai aparecer na lista para o atleta escolher sozinho ao se inscrever.<br>
        <strong>Fechada:</strong> existe aqui, mas não aparece para o atleta — o vínculo é decidido por você.
    </small>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="active" value="0">
        <input class="custom-control-input" id="active" name="active" type="checkbox" value="1"
               {{ old('active', $team?->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Equipe ativa</label>
    </div>
    <small class="form-text text-muted">Desative para aposentar uma equipe sem apagar o histórico dela.</small>
</div>
