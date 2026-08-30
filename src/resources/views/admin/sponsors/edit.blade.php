@extends('layouts.admin')

@section('titulo', 'Editar patrocinador')
@section('icone', 'award')
@section('subtitulo', $sponsor->name)

@section('conteudo')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados do patrocinador</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.patrocinadores.update', $sponsor->id) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.sponsors._form', ['sponsor' => $sponsor])
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                        <a class="btn btn-link" href="{{ route('admin.patrocinadores.index') }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
