@extends('layouts.auth')

@section('title','Inscrição')

@section('content')

<div class="modal-overlay">

<div class="modal text-left">

<form method="POST" action="/registrations/1" class="form-container">

@csrf

<h2 class="event-title">
CarnaRun do Quarteto - 2025
</h2>

<div class="athlete-info">
    <span>👤 Atleta:</span>
    <strong>{{ Auth::user()->name }}</strong>
</div>
<p><hr></p>
<select name="modality_id" required>

<option value="">Modalidade</option>

<option value="1">
3km Caminhada
</option>

<option value="2">
5km Caminhada
</option>

<option value="3">
5km Corrida
</option>

<option value="4">
10km Corrida
</option>

</select>

<select name="kit_id" required>

<option value="">kit</option>

<option value="1">
Kit Básico
</option>

<option value="2">
Só medalha
</option>

<option value="3">
Kit PCD
</option>

</select>

<button type="submit" class="btn-primary">
Confirmar inscrição
</button>

</form>

</div>

</div>

@endsection