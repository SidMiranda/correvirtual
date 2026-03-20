@extends('layouts.app')

@section('title', 'Eventos - Corre Virtual')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-cards.css') }}">
@endpush

@section('content')

<x-app.main-banner />

  <div class="container">
    <div class="cards-grid">

      <a class="event-card" href="{{ url('/event/1') }}">
        <div class="event-card__image-wrapper">
          <img src="{{ asset('img/carnarun-2025.jpg') }}" class="event-card__image" alt="CarnaRun">
          <div class="event-card__share">🔗</div>
        </div>
        <div class="event-card__date">
          <span class="event-card__date-day">03</span>
          <span class="event-card__date-month">MAR</span>
        </div>

        <div class="event-card__content">
          <span class="event-card__category">Corrida de Rua</span>
          <h2 class="event-card__title">CarnaRun do Quarteto - 2025</h2>

          <div class="event-card__footer">
            <div class="event-card__location">📍 Mogi Guaçu, SP</div>
            <div class="event-card__distances">5k 10k</div>
          </div>
        </div>
      </a>

      <div class="event-card">
        <div class="event-card__image-wrapper">
          <img src="{{ asset('img/desafio-virtual-25.jpg') }}" class="event-card__image" alt="Desafio Virtual">
          <div class="event-card__share">🔗</div>
        </div>
        <div class="event-card__date">
          <span class="event-card__date-day">31</span>
          <span class="event-card__date-month">JUL</span>
        </div>
        <div class="event-card__content">
          <span class="event-card__category">Caminhada</span>
          <h2 class="event-card__title">Desafio Virtual Pastelicia</h2>
          <div class="event-card__footer">
            <div class="event-card__location">📍 Mogi Guaçu, SP</div>
            <div class="event-card__distances">5k 21k</div>
          </div>
        </div>
      </div>

      <div class="event-card">
        <div class="event-card__image-wrapper">
          <img src="{{ asset('img/halloween .jpg') }}" class="event-card__image" alt="Halloween">
          <div class="event-card__share">🔗</div>
        </div>
        <div class="event-card__date">
          <span class="event-card__date-day">31</span>
          <span class="event-card__date-month">OUT</span>
        </div>
        <div class="event-card__content">
          <span class="event-card__category">Treinão</span>
          <h2 class="event-card__title">Corre que a Bruxa Vem Aí</h2>
          <div class="event-card__footer">
            <div class="event-card__location">📍 Mogi Guaçu, SP</div>
            <div class="event-card__distances">3k 5k</div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection
