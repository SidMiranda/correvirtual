@extends('layouts.app')

@section('title', 'Eventos - Corre Virtual')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-cards.css') }}">
@endpush

@section('content')

    <x-app.main-banner />

    <div class="container">
        <h2 class="block-header-title">
            CALENDÁRIO <span> EVENTOS </span> 2026
        </h2>

        <div class="cards-grid">
            @foreach($events as $event)
                <x-app.event-card :event="$event" />
            @endforeach
        </div>
    </div>

    <div class="container">
        <h2 class="block-header-title">
            SOBRE <span> NOS </span>
        </h2>

        <x-app.about />
    </div>



    <div class="container">
        <h2 class="block-header-title">

        </h2>

        <x-app.sponsors />

    </div>

    <x-app.foot />

@endsection
