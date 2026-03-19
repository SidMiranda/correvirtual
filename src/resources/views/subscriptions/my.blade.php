@extends('layouts.app')

@section('title', 'Minhas Inscrições - Corre Virtual')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/top-bar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/my-registrations.css') }}">
@endpush

@section('content')
  <div class="container">
    
    {{-- 
      MOCK: Condição alterada para exibir sempre os dados mockados temporariamente.
      No futuro, remova "false && " para voltar a testar o empty state se não houver inscrições.
    --}}
    @if(false && $registrations->isEmpty())
      <div class="empty-container">
        <div class="empty-state-wrapper">
          <h2 class="my-registrations-title">Minhas Inscrições</h2>
          <div class="empty-registrations-card">
            <div class="empty-icon">🏃‍♂️</div>
            <p>Você ainda não possui nenhuma inscrição.</p>
            <a href="{{ url('/') }}" class="btn-back-calendar">Voltar para o calendário</a>
          </div>
        </div>
      </div>
    @else
      <h2 class="my-registrations-title">Minhas Inscrições</h2>
      
      <!-- Seção: Próximos Eventos -->
      <h3 class="section-title">Próximos Eventos</h3>
      <div class="registrations-list">
        
        <!-- Mock: Evento Ativo 1 -->
        <div class="registration-list-card">
            <div class="registration-list-card__image-wrapper">
                <img src="{{ asset('img/carnarun-2025.jpg') }}" alt="CarnaRun do Quarteto">
            </div>
            <div class="registration-list-card__content">
                <div class="registration-list-card__header">
                    <h4 class="registration-list-card__title">CarnaRun do Quarteto - 2025</h4>
                    <span class="status-badge status-active">Inscrição Confirmada</span>
                </div>
                <div class="registration-list-card__info">
                    <p>📍 São Paulo, SP (Parque Ibirapuera)</p>
                    <p>📅 25/02/2025 às 07:00</p>
                    <p>🏃 5km Corrida</p>
                    <p>🎒 Kit Básico + Camiseta</p>
                </div>
                <div class="registration-list-card__actions">
                    <a href="#" class="btn-secondary">Ver Detalhes</a>
                </div>
            </div>
        </div>

        <!-- Mock: Evento Ativo 2 (Pendente) -->
        <div class="registration-list-card">
            <div class="registration-list-card__image-wrapper">
                <img src="{{ asset('img/halloween .jpg') }}" alt="Night Run">
            </div>
            <div class="registration-list-card__content">
                <div class="registration-list-card__header">
                    <h4 class="registration-list-card__title">Night Run XP - Etapa 1</h4>
                    <span class="status-badge status-pending">Pagamento Pendente</span>
                </div>
                <div class="registration-list-card__info">
                    <p>📍 Rio de Janeiro, RJ (Copacabana)</p>
                    <p>📅 15/03/2025 às 20:00</p>
                    <p>🏃 10km Corrida</p>
                    <p>🎒 Kit VIP</p>
                </div>
                <div class="registration-list-card__actions">
                    <a href="#" class="btn-primary-small">Pagar Agora</a>
                    <a href="#" class="btn-secondary">Ver Detalhes</a>
                </div>
            </div>
        </div>

      </div>

      <!-- Seção: Eventos Passados -->
      <h3 class="section-title">Eventos Passados</h3>
      <div class="registrations-list">

        <!-- Mock: Evento Passado 1 -->
        <div class="registration-list-card past-event">
            <div class="registration-list-card__image-wrapper">
                <img src="{{ asset('img/desafio-virtual-25.jpg') }}" alt="Desafio de Verão">
            </div>
            <div class="registration-list-card__content">
                <div class="registration-list-card__header">
                    <h4 class="registration-list-card__title">Desafio de Verão 2024</h4>
                    <span class="status-badge status-past">Concluído</span>
                </div>
                <div class="registration-list-card__info">
                    <p>📍 Florianópolis, SC</p>
                    <p>📅 10/01/2024 às 08:00</p>
                    <p>🏃 3km Caminhada</p>
                    <p>🎒 Só Medalha</p>
                </div>
                <div class="registration-list-card__actions">
                    <a href="#" class="btn-secondary">Ver Certificado</a>
                </div>
            </div>
        </div>

      </div>

      {{-- 
      Futuro loop com dados dinâmicos:
      @foreach($registrations as $registration)
        ...
      @endforeach
      --}}

    @endif
  </div>
@endsection
