@extends('layouts.admin')

@section('titulo', 'Editar equipe')
@section('icone', 'users')
@section('subtitulo', $team->name)

@section('conteudo')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados da equipe</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.equipes.update', $team->id) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.teams._form', ['team' => $team])
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Salvar alterações</button>
                        <a class="btn btn-link" href="{{ route('admin.equipes.index') }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
