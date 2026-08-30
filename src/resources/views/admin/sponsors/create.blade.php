@extends('layouts.admin')

@section('titulo', 'Novo patrocinador')
@section('icone', 'award')

@section('conteudo')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados do patrocinador</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.patrocinadores.store') }}">
                        @csrf
                        @include('admin.sponsors._form')
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Criar patrocinador</button>
                        <a class="btn btn-link" href="{{ route('admin.patrocinadores.index') }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
