@extends('layouts.admin')

@section('titulo', 'Novo evento')
@section('icone', 'calendar')
@section('subtitulo', 'Cadastre a corrida ou o desafio virtual')

@section('conteudo')

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados do evento</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.eventos.store') }}">
                        @csrf

                        @include('admin.events._form')

                        <hr class="my-4">

                        <button class="btn btn-primary" type="submit">Criar evento</button>
                        <a class="btn btn-link" href="{{ route('admin.eventos.index') }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
