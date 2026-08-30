@extends('layouts.app')

@section('title', $event->title . ' - Corre Virtual')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/top-bar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/info-evento.css') }}?v={{ filemtime(public_path('css/info-evento.css')) }}">
@endpush

@section('content')
    @if($event->banner_url)
        <div class="banner-wrap">
            <a class="back-button" href="{{ url('/') }}">← Voltar</a>
            <section class="event-banner">
                <img src="{{ \App\Support\Arquivos::bannerDoEvento($event) }}"
                     onerror="this.onerror=null;this.src='{{ \App\Support\Arquivos::bannerPadrao() }}';"
                     alt="Banner do evento {{ $event->title }}" class="banner-img">
            </section>
        </div>
    @else
        {{-- <a class="back-button md-mt-5" href="{{ url('/') }}">← Voltar</a> --}}
    @endif

    <h2 class="block-header-title">
        {{ $event->title }}
    </h2>

  @error('inscricao')
    <div class="event-erro-inscricao" role="alert">{{ $message }}</div>
  @enderror

  <section class="event-content">

    <aside class="event-side">
      <div class="event-info-box">
        <h3>Data</h3>
        <p>{{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y \à\s H:i') }}</p>
      </div>

      <div class="event-info-box">
        <h3>Local</h3>
        <p>{{ $event->location }}</p>
      </div>

      {{-- Prova já realizada ou com prazo encerrado não oferece inscrição: o
           botão levaria a pessoa a um formulário que não vai aceitar nada.
           Esconder aqui é só a metade visível — quem barra de verdade é o
           SubscribeController, porque o endereço pode ser digitado à mão. --}}
      @if ($event->inscricoesAbertas())
        <a href="/subscribe/event/{{ $event->id }}" class="cta-button">
          Inscreva-se
        </a>
      @elseif ($event->jaAconteceu())
        <div class="event-aviso event-aviso--realizado">
          <strong>Evento realizado</strong>
          <span>Aconteceu em {{ $event->event_date->format('d/m/Y') }}.</span>
        </div>
      @else
        <div class="event-aviso">
          <strong>Inscrições encerradas</strong>
          <span>O prazo terminou em {{ $event->registration_deadline->format('d/m/Y \à\s H:i') }}.</span>
        </div>
      @endif

    </aside>

    <div class="event-details">

      <div class="info-block">
        <h2>Descrição</h2>
        <p>{{ $event->description }}</p>
      </div>

      <div class="info-block">
        <h2>Kits</h2>
        @if($event->kits && $event->kits->count() > 0)
          <div class="kits-list">
            @foreach($event->kits as $kit)
              <div class="kit-card" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; margin-bottom: 16px; background-color: #fafafa;">
                <h3 style="margin-top: 0; margin-bottom: 8px; color: #333;">{{ $kit->name }}</h3>
                <p style="margin-top: 0; margin-bottom: 12px; color: #666;">{{ $kit->description }}</p>
                <p style="font-size: 1.5em; font-weight: bold; margin: 0; color: #111;">
                  R$ {{ number_format($kit->price, 2, ',', '.') }}
                </p>
              </div>
            @endforeach
          </div>
        @else
          <p>Nenhum kit disponível ainda.</p>
        @endif
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
        <p><strong>Encerramento das inscrições:</strong> {{ \Carbon\Carbon::parse($event->registration_deadline)->format('d/m/Y \à\s H:i') }}</p>
      </div>

    </div>
  </section>

  <x-app.foot />
@endsection
