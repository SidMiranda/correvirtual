@extends('layouts.app')

@section('title', 'Eventos - Corre Virtual')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-cards.css') }}">
@endpush

@section('content')

<x-app.main-banner />

  <div class="container">
    <div class="cards-grid">

    @foreach($events as $event)
        <x-app.event-card :event="$event" />
    @endforeach

    </div>
  </div>
@endsection
