@extends('layouts.admin')

@section('titulo', 'Editar kit')
@section('icone', 'package')
@section('subtitulo', $event->title . ' — ' . $kit->name)

@section('conteudo')
    @include('admin._abas-do-evento')

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados do kit</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.eventos.kits.update', [$event->id, $kit->id]) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.kits._form', ['kit' => $kit])
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                        <a class="btn btn-link" href="{{ route('admin.eventos.kits.index', $event->id) }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
