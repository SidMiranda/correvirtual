@php $kit = $kit ?? null; @endphp

<div class="form-group">
    <label class="small mb-1" for="name">Nome do kit</label>
    <input class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" type="text" required maxlength="255"
           placeholder="Ex.: Kit Camiseta, Kit Digital"
           value="{{ old('name', $kit?->name) }}">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="small mb-1" for="description">O que vem no kit</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="3"
              placeholder="Ex.: Camiseta + medalha + número de peito">{{ old('description', $kit?->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="price">Preço (R$)</label>
            <input class="form-control @error('price') is-invalid @enderror"
                   id="price" name="price" type="number" step="0.01" min="0.01" required
                   placeholder="Ex.: 79.90"
                   value="{{ old('price', $kit?->price) }}">
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="form-text text-muted">
                    É este o valor cobrado no Pix. A inscrição guarda o preço do momento em que foi criada,
                    então mudar aqui não altera quem já se inscreveu.
                </small>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="small mb-1" for="stock">Estoque</label>
            <input class="form-control @error('stock') is-invalid @enderror"
                   id="stock" name="stock" type="number" min="0"
                   placeholder="Deixe em branco para não controlar"
                   value="{{ old('stock', $kit?->stock) }}">
            @error('stock')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="form-text text-muted">O sistema ainda não bloqueia a venda ao zerar — hoje é só informativo.</small>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="active" value="0">
        <input class="custom-control-input" id="active" name="active" type="checkbox" value="1"
               {{ old('active', $kit?->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Kit ativo</label>
    </div>
    <small class="form-text text-muted">Só kits ativos aparecem para o atleta escolher.</small>
</div>
