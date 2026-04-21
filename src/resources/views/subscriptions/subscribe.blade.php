@extends('layouts.auth')

@section('title', 'Inscrição')

@section('content')

    <div class="modal-overlay">

        <div class="modal text-left">

            <form method="POST" action="/subscribe/event/{{ $event->id }}" class="form-container">

                @csrf

                <h2 class="event-title">
                    {{ $event->title }}
                </h2>

                <div class="athlete-info">
                    <span>👤 Atleta:</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <p>
                    <hr>
                </p>
                <select name="modality_id" required>

                    <option value="">Modalidade</option>
                    @foreach($event->modalities as $modality)
                        <option value="{{ $modality->id }}">
                            {{ $modality->name }}
                        </option>
                    @endforeach
                </select>

                <select name="kit_id" required>

                    <option value="">kit</option>
                    @foreach($event->kits as $kit)
                        <option value="{{ $kit->id }}">
                            {{ $kit->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary">
                    Confirmar inscrição
                </button>

            </form>

        </div>

    </div>

@endsection
