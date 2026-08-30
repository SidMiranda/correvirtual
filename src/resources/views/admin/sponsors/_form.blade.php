@php $sponsor = $sponsor ?? null; @endphp

<div class="form-group">
    <label class="small mb-1" for="name">Nome do patrocinador</label>
    <input class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" type="text" required maxlength="255"
           placeholder="Ex.: Pastelaria Pastelícia, Mega Gelo & Chopp"
           value="{{ old('name', $sponsor?->name) }}">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="site_url">Site</label>
    <input class="form-control @error('site_url') is-invalid @enderror"
           id="site_url" name="site_url" type="url" maxlength="255"
           placeholder="https://exemplo.com.br"
           value="{{ old('site_url', $sponsor?->site_url) }}">
    @error('site_url')
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <small class="form-text text-muted">
            Opcional. Com site preenchido, o logo vira link no site — é metade do valor de aparecer lá.
            Precisa começar com <strong>https://</strong>.
        </small>
    @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="logo">Logo</label>

    <div class="d-flex align-items-center">
        @if ($sponsor?->has_logo)
            <img src="{{ \App\Support\Arquivos::logoDoPatrocinador($sponsor) }}"
                 alt="Logo atual" class="logo-patrocinador logo-patrocinador--grande mr-3"
                 onerror="this.style.display='none';">
        @endif

        <input class="form-control-file @error('logo') is-invalid @enderror"
               id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
    </div>

    @error('logo')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @else
        <small class="form-text text-muted">
            Opcional. <strong>PNG com fundo transparente</strong> é o que fica melhor — o logo aparece
            direto sobre o fundo da página. JPG e WEBP também servem. Até 5 MB.
            Deixe em branco para manter o atual.
        </small>
    @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="description">Observação interna</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="2"
              placeholder="Opcional — contato, vigência do contrato, o que foi combinado">{{ old('description', $sponsor?->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <small class="form-text text-muted">Só para você. Não aparece no site.</small>
    @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="position">Ordem</label>
    <input class="form-control @error('position') is-invalid @enderror"
           id="position" name="position" type="number" min="0" max="999" style="max-width: 140px;"
           value="{{ old('position', $sponsor?->position ?? 0) }}">
    @error('position')
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <small class="form-text text-muted">
            Menor aparece primeiro. Quem tiver o mesmo número entra por ordem de nome.
        </small>
    @enderror
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="active" value="0">
        <input class="custom-control-input" id="active" name="active" type="checkbox" value="1"
               {{ old('active', $sponsor?->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Aparece no site</label>
    </div>
    <small class="form-text text-muted">
        Desative para tirar do site quando o contrato acabar, sem apagar o cadastro.
    </small>
</div>
