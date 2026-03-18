@extends('layouts.app')

@section('title', 'Minhas Inscrições - Corre Virtual')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/top-bar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/my-registrations.css') }}">
@endpush

@section('content')
  <div class="container {{ $registrations->isEmpty() ? 'empty-container' : '' }}">
    @if($registrations->isEmpty())
      <div class="empty-state-wrapper">
        <h2 class="my-registrations-title">Minhas Inscrições</h2>
        <div class="empty-registrations-card">
          <div class="empty-icon">🏃‍♂️</div>
          <p>Você ainda não possui nenhuma inscrição.</p>
          <a href="{{ url('/') }}" class="btn-back-calendar">Voltar para o calendário</a>
        </div>
      </div>
    @else
      <h2 class="my-registrations-title">Minhas Inscrições</h2>
      <div class="registrations-grid">
        @foreach($registrations as $registration)
          <div class="registration-card">
            <div class="registration-card__image-wrapper">
              <img src="{{ asset('storage/' . $registration->event->thumbnail) }}" class="registration-card__image" alt="{{ $registration->event->name }}">
            </div>
            <div class="registration-card__content">
              <h3 class="registration-card__title">{{ $registration->event->name }}</h3>
              <p class="registration-card__date">{{ $registration->event->event_date->format('d/m/Y') }}</p>
              <p class="registration-card__status">Status: {{ $registration->status }}</p>
              <a href="#" class="registration-card__button">Ver Detalhes</a>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
