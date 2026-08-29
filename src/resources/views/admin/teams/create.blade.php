@extends('layouts.admin')

@section('titulo', 'Nova equipe')
@section('icone', 'users')

@section('conteudo')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Dados da equipe</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.equipes.store') }}">
                        @csrf
                        @include('admin.teams._form')
                        <hr class="my-4">
                        <button class="btn btn-primary" type="submit">Criar equipe</button>
                        <a class="btn btn-link" href="{{ route('admin.equipes.index') }}">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
