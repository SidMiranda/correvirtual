@extends('layouts.admin')

@section('titulo', 'Novo kit')
@section('icone', 'package')
@section('subtitulo', $event->title)

@section('conteudo')
    @include('admin._abas-do-evento')

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados do kit</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.eventos.kits.store', $event->id) }}">
                        @csrf
                        @include('admin.kits._form')
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Criar kit</button>
                        <a class="btn btn-link" href="{{ route('admin.eventos.kits.index', $event->id) }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
