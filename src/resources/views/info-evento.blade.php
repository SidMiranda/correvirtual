@extends('layouts.app')

@section('title', 'Descrição do Evento - Corre Virtual')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/info-evento.css') }}">
@endpush

@section('content')
  <div class="banner-wrap">
    <a class="back-button" href="{{ url('/') }}">← Voltar</a>
    <section class="event-banner">
      <img src="{{ asset('img/banner-evento.jpg') }}" alt="Banner do evento" class="banner-img">
    </section>
  </div>

  <section class="event-content">

    <aside class="event-side">
      <div class="event-info-box">
        <h3>Data</h3>
        <p>03 de Março de 2026</p>
      </div>

      <div class="event-info-box">
        <h3>Local</h3>
        <p>Av. 9 de Abril<br>Mogi Guaçu - SP</p>
      </div>

      <a href="/registrations/1/pay" class="cta-button">
        Inscreva-se
      </a>

    </aside>

    <div class="event-details">

      <div class="info-block">
        <h2>Descrição</h2>
        <p>O CarnaRun do Quarteto - 2025 é uma experiência completa que vai além da corrida.</p>
      </div>

      <div class="info-block">
        <h2>Cronograma</h2>
        <p>
          04h - Abertura do estacionamento<br>
          05h30 - Largada 10km<br>
          06h - Largada 5km<br>
          08h30 - Premiação
        </p>
      </div>

      <div class="info-block">
        <h2>Inscrição</h2>
        <p>A inscrição dá direito ao kit exclusivo do evento.</p>
      </div>

    </div>
  </section>
@endsection