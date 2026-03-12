@extends('layouts.auth')

@section('title','Inscrição')

@section('content')

<div class="modal-overlay" style="display:flex">

<div class="modal">

<form method="POST" action="/registrations/store" class="form-container">

@csrf

<h2 class="event-title">
{{ $event['name'] }}
</h2>

<div class="athlete-box">
👤 Atleta: <strong>{{ Auth::user()->name }}</strong>
</div>

<label>Modalidade</label>

<select name="distance" required>

<option value="">Escolha</option>

<option value="3k_walk">
3km Caminhada
</option>

<option value="5k_run">
5km Corrida
</option>

<option value="10k_run">
10km Corrida
</option>

</select>

<label>Kit</label>

<select name="kit" required>

<option value="">Escolha</option>

<option value="basic">
Kit Básico
</option>

<option value="medal">
Só medalha
</option>

<option value="pcd">
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