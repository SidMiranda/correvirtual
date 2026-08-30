@extends('layouts.app-v2')

@section('title', 'Eventos - Corre Virtual')

@push('styles')
    {{-- ?v={{ filemtime(...) }}: sem isso uma mudança de estilo só aparece para
         quem limpar o cache do navegador. Mesmo cuidado já tomado na Home v2. --}}
    <link rel="stylesheet" href="{{ asset('css/event-cards.css') }}?v={{ filemtime(public_path('css/event-cards.css')) }}">
@endpush

@section('content')

    <x-app.banner-v2 />

    <div class="container" id="eventos">
        <h2 class="block-header-title">
            PRÓXIMOS <span> EVENTOS </span>
        </h2>

        @if($proximosEventos->isEmpty())
            <p class="eventos-vazio">
                Nenhuma prova com inscrição aberta no momento. Fique de olho — em breve tem mais.
            </p>
        @else
            <div class="cards-grid">
                @foreach($proximosEventos as $event)
                    <x-app.event-card :event="$event" />
                @endforeach
            </div>
        @endif
    </div>

    @if($eventosPassados->isNotEmpty())
        {{-- Provas já realizadas continuam na página: servem de vitrine para
             quem está chegando agora e conhecendo o organizador. --}}
        <div class="container" id="eventos-passados">
            <h2 class="block-header-title">
                EVENTOS <span> REALIZADOS </span>
            </h2>

            <div class="cards-grid">
                @foreach($eventosPassados as $event)
                    <x-app.event-card :event="$event" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="container" id="sobre">
        <h2 class="block-header-title">
            SOBRE <span> NOS </span>
        </h2>

        <x-app.about />
    </div>

    <div class="container" id="patrocinadores">
        <h2 class="block-header-title">
            NOSSOS <span> PATROCINADORES </span>
        </h2>

        <x-app.sponsors />

    </div>

    <x-app.foot />

@endsection
